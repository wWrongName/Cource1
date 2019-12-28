import telebot
import json
import requests
import time
import datetime
import re
from bs4 import BeautifulSoup
from telebot import apihelper

retVal = '-1'
file = 'users.json'
days = ['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье']
#============================================================================================================================|
#---------------------------------parse html file and get a suitable item----------------------------------------------------|
#============================================================================================================================|
def levDistance(dictionary, checkedStr):
    secondStr = checkedStr.lower()
    for i in range(7):
        if len(dictionary[i]) - len(secondStr) != 0:
            continue        
        print(dictionary[i])
        counter = 0
        firstStr = dictionary[i].lower()
        for j in range(len(firstStr)):
            if firstStr[j] != secondStr[j]:
                counter += 1
        if counter <= 2:
            return dictionary[i]
    return '-1'
#----------------------------------------------------------------------------------------------------------------------------|
def checkUser(teleId, teleJson):
    with open(teleJson) as teleFile:
        teleList = json.load(teleFile)
        for i in range(len(teleList)):
            if teleList[i]["id"] == teleId:
                return 1
    with open(teleJson, 'w') as teleFile:
        teleList.append({"id": teleId, "who": "-1", "name":"-1", "username":"", "token":""})
        json.dump(teleList, teleFile)
        return 0
#----------------------------------------------------------------------------------------------------------------------------|
def readChoice(teleId, teleJson):
    with open(teleJson) as teleFile:
        teleList = json.load(teleFile)
        for i in range(len(teleList)):
            if teleList[i]["id"] == teleId and teleList[i]["name"] != '-1':
                return teleList[i]["who"]
        return '0'
#----------------------------------------------------------------------------------------------------------------------------|
def writeChoice(teleId, teleJson, who, name):
    with open(teleJson) as teleFile:
        teleList = json.load(teleFile)
        for i in range(len(teleList)):
            if teleList[i]["id"] == teleId:
                teleFile = open(teleJson, 'w')
                teleList[i]["who"] = who
                teleList[i]["name"] = name
                json.dump(teleList, teleFile)
#----------------------------------------------------------------------------------------------------------------------------|
def getName(teleId, teleJson):
    with open(teleJson) as teleFile:
        teleList = json.load(teleFile)
        for i in range(len(teleList)):
            if teleList[i]["id"] == teleId:
                return teleList[i]["name"]
#----------------------------------------------------------------------------------------------------------------------------|
def findUserByName(name, teleJson):
    with open(teleJson) as teleFile:
        teleList = json.load(teleFile)
        for i in range(len(teleList)):
            if teleList[i]["username"] == name:
                return teleList[i]["id"]
        return '-1'
#----------------------------------------------------------------------------------------------------------------------------|
def dayRussification(day):
    if day == 'Sun':
        return days[6]
    elif day == 'Mon':
        return days[0]
    elif day == 'Tue':
        return days[1]
    elif day == 'Wed':
        return days[2]
    elif day == 'Thu':
        return days[3]
    elif day == 'Fri':
        return days[4]
    elif day == 'Sat':
        return days[5]
#----------------------------------------------------------------------------------------------------------------------------|
def getLesson(tDelta):
    thatTime = datetime.datetime.today()
    pHour = thatTime.hour
    pMin = thatTime.minute

    retArray = ['1', 'Первая перемена', '2', 'Вторая перемена', '3', 'Третья перемена', '4', 'Четвёртая перемена', '5', 'Пятая перемена', '6', '0']
    
    #first lesson
    if (pHour >= 9 and pHour < 10) or (pHour == 10 and pMin < 30):
        return retArray[0 + tDelta]
    elif pHour == 10 and pMin >= 30 and pMin < 40:
        return retArray[1 + tDelta]

    #second lesson
    elif (pHour == 10 and pMin >= 40) or pHour == 11 or (pHour == 12 and pMin < 10):
        return retArray[2 + tDelta]
    elif pHour == 12 and pMin >= 10 and pMin < 20:
        return retArray[3 + tDelta]

    #third lesson
    elif (pHour == 12 and pMin >= 20) or (pHour == 13 and pMin < 50):
        return retArray[4 + tDelta]
    elif (pHour == 13 and pMin >= 50) or (pHour == 14 and pMin < 10):
        return retArray[5 + tDelta]

    #fourth lesson
    elif (pHour == 14 and pMin >= 10) or (pHour == 15 and pMin < 40):
        return retArray[6 + tDelta]
    elif pHour == 15 and pMin >= 40 and pMin < 50:
        return retArray[7 + tDelta]

    #fifth lesson
    elif (pHour == 15 and pMin >= 50) or pHour == 16 or (pHour == 17 and pMin < 20):
        return retArray[8 + tDelta]
    elif pHour == 17 and pMin >= 20 and pMin < 30:
        return retArray[9 + tDelta]

    #sixth lesson
    elif (pHour == 17 and pMin >= 30) or (pHour >= 18 and pHour < 19):
        return retArray[10 + tDelta]
    else:
        return retArray[11]
#----------------------------------------------------------------------------------------------------------------------------|
def checkID(textID, who):
    page = requests.get("http://rasp.guap.ru/Default.aspx")
    raspGuap = BeautifulSoup(page.text, 'lxml')
    if who == 'g':
        list = raspGuap.find('option', text = textID)
    elif who == 'p':
        list = raspGuap.find('option', text = re.compile(textID))
    if list is None:
        return '-1'
    else:
        print(list.get('value'))
        return list.get('value')
#----------------------------------------------------------------------------------------------------------------------------|
def wholeDaySchedule(day, who, ID):
    page = requests.get("http://rasp.guap.ru/?" + who + "=" + ID)
    raspGuap = BeautifulSoup(page.text, 'lxml')
    weekType = raspGuap.find('p').em.get('class')
    gettingSchedule = []
    firstDayGone = 0
    for child in raspGuap.recursiveChildGenerator():
        if firstDayGone == 1:
            if child.name == 'h4':
                gettingSchedule.append('\n' + child.get_text())
            elif child.name == 'span':
                gettingSchedule.append(child.get_text())
        if child.name == 'h3':
            if child.get_text() == day and firstDayGone == 0:
                firstDayGone = 1
            elif firstDayGone == 1:
                break
    if gettingSchedule == []:
        return 'Занятий нет'
    else:
        retStr = ''
        if weekType == ['up']:
            retStr += 'Сейчас верхняя неделя ▲\n'
        else:
            retStr += 'Сейчас нижняя неделя ▼\n'
        for i in range(len(gettingSchedule)):
                retStr += gettingSchedule[i] + '\n'
        return retStr
#----------------------------------------------------------------------------------------------------------------------------|
def daySchedule(day, who, ID, plusTime):
    page = requests.get("http://rasp.guap.ru/?" + who + "=" + ID)
    raspGuap = BeautifulSoup(page.text, 'lxml')
    #get weekType (up or down)
    weekType = raspGuap.find('p').em.get('class')
    gettingSchedule = []
    firstDayGone = 0
    lessonFlag = 0
    #get name of lesson, room and teacher
    for child in raspGuap.recursiveChildGenerator():
        if lessonFlag == 1:
            if child.name == 'h4':
                break
            if child.name == 'span':
                gettingSchedule.append(child.get_text())
        if (firstDayGone == 1 and child.name == 'h4' and child.get_text().find(getLesson(plusTime)) == 0):
            lessonFlag = 1
            gettingSchedule.append(child.get_text())
        if child.name == 'h3':
            if child.get_text() == day and firstDayGone == 0:
                firstDayGone = 1
            elif firstDayGone == 1:
                break
    #return an information to the user
    if gettingSchedule == []:
        if len(getLesson(plusTime)) > 1:
            return getLesson(plusTime)
        else:
            return 'Занятий нет'
    else:
        retStr = ''
        length = len(gettingSchedule)
        if weekType == ['up']:
            if length == 7:
                length = 4
        else:
            if length == 7:
                retStr += gettingSchedule[0] + '\n' + gettingSchedule[4] + '\n' + gettingSchedule[5] + '\n' + gettingSchedule[6] + '\n' 
                return retStr
        for i in range(length):
                retStr += gettingSchedule[i] + '\n'
        return retStr
#============================================================================================================================|

apihelper.proxy = {'https': 'https:51.158.123.35:8811'}
bot = telebot.TeleBot('869456428:AAEIGO4PyJgzN9BH98DEDO6ah5ADSmBtN1Y')
@bot.message_handler(content_types=['text'])

#============================================================================================================================|
#-------------------------------------------main bot function----------------------------------------------------------------|
#============================================================================================================================|
def get_text_messages(message):
    print("dfsdfsd");
    global retVal
    who = readChoice(message.from_user.id, file)
    if checkUser(message.from_user.id, file) == 0:
            print('New user')
    if message.text == "/start":
        bot.send_message(message.from_user.id, "Здравствуйте, я бот-навигатор по расписанию ГУАП. Напишите 'Справка', чтобы узнать доступные команды.")
#----------------------------------------------------------------------------------------------------------------------------| 
    elif message.text.lower() == "справка":
        bot.send_message(message.from_user.id,
"Бот-навигатор может:\n\
- показать ближайшую по времени пару\n\
- показать список пар на определённый день\n\
- работать с сервисом \"THE DIARY\" (вход и сброс пароля)\n\
Примеры команд:\n\
- группа 3845\n\
- преподаватель Жиданов К.А.\n\
- где пара?\n\
- расписание на понедельник\n\
- вход user pass")
#----------------------------------------------------------------------------------------------------------------------------|
    elif message.text.lower().find('группа') == 0:
        group = message.text.split(' ')
        if len(group) >= 2:
            retVal = checkID(group[1], 'g')
        else:
            retVal = '-1'
        if retVal != '-1':
            writeChoice(message.from_user.id, file, 'g', retVal)
            bot.send_message(message.from_user.id, "Отлично, теперь вы можете узнать расписание для группы " + group[1])
        else:
            bot.send_message(message.from_user.id, "Группа не найдена. Проверьте правильность введённых данных.")
#----------------------------------------------------------------------------------------------------------------------------|
    elif message.text.lower().find('где пара') == 0 and who != '0':
        name = getName(message.from_user.id, file)
        ptrStr = daySchedule(dayRussification(time.strftime("%a")), who, name, 0)
        bot.send_message(message.from_user.id, 'Сейчас:\n' + ptrStr)
        ptrStr = daySchedule(dayRussification(time.strftime("%a")), who, name, 1)
        bot.send_message(message.from_user.id, 'Далее:\n' + ptrStr)
        print(message.from_user.id)
#----------------------------------------------------------------------------------------------------------------------------|
    elif message.text.lower().find('преподаватель') == 0:
        teacher = message.text.split(' ')
        if len(teacher) >= 3:
            SNM = teacher[1] + ' ' + teacher[2]
            retVal = checkID(SNM, 'p')
        else:
            retVal = '-1'
        if retVal != '-1':
            writeChoice(message.from_user.id, file, 'p', retVal)
            bot.send_message(message.from_user.id, "Отлично, теперь вы можете узнать расписание для преподавателя " + SNM)
        else:
            bot.send_message(message.from_user.id, "Преподаватель не найден. Проверьте правильность введённых данных.")
#----------------------------------------------------------------------------------------------------------------------------|
    elif message.text.lower().find('расписание на') == 0:
        outsideMsg = message.text.split(' ')
        if len(outsideMsg) == 3:
            newDay = outsideMsg[2].capitalize()
            newDay = levDistance(days, newDay)
            if newDay != '-1':
                name = getName(message.from_user.id, file)
                bot.send_message(message.from_user.id, wholeDaySchedule(newDay, who, name))
            else:
                bot.send_message(message.from_user.id, "Неверный запрос. Проверьте правильность введённых данных.")
        else:
            bot.send_message(message.from_user.id, "Неверный запрос. Проверьте правильность введённых данных.")
    elif message.text.lower().find('вход') == 0:
        clst = message.text.split(' ')
        if len(clst) >= 3:
            I = 0
            req = requests.post("http://localhost/cource1/Cource1/commandsAllocator/getToken", json = {"user":clst[1],"pass":clst[2]})
            print(clst[1])
            if (req.status_code == 200):
                print("dsf")
                with open(file) as teleFile:
                    teleList = json.load(teleFile)
                    for i in range(len(teleList)):
                        if teleList[i]["id"] == message.from_user.id:
                            I = i
                with open(file, 'w') as teleFile:
                    teleList[I]["token"] = json.loads(req.text)["token"]
                    teleList[I]["username"] = clst[1]
                    json.dump(teleList, teleFile)
                bot.send_message(message.from_user.id, "Отлично, вы авторизовались под именем " + clst[1] + ". Теперь вам доступно восстановление аккаунта при утере пароля.")
            else:
                bot.send_message(message.from_user.id, "Упсс, что-то пошло не так, проверьте правильность введённых данных")
    else:
        bot.send_message(message.from_user.id, 'Я вас не понимаю. Для получения списка доступных команд напишите "Справка".')
#============================================================================================================================|
#----------------------------------------none stop sending requests----------------------------------------------------------|
#============================================================================================================================|
bot.polling(none_stop=True, interval=0)
