import requests

url = 'https://www.nseindia.com/api/historical/cm/equity?symbol=INFY&series=[%22EQ%22]&from=08-07-2020&to=08-08-2020&csv=true'

headers = {'accept-encoding':'gzip, deflate',
       'accept-language': "en-US,en;q=0.9",
       'user-agent': "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36"
    }
cookie_dict = {'bm_sv' : 'EE2F5FAB6D197878346C5833E9E64358~Fw/XpPVQyfoErb/vrj/veSaqiNffHbI653XS2cYMEFOmIuioQNH76UFd++2vRJndHuhy1ASupcUFZVgmrUayFZwWBtJMRWI+hUqjPH+5jYZ/9HvJ0csvJUuuZh6ZNDiaZEvGur5IYQL8sUKgvTeamHvuSRWy0BJENVgIhAqkjBI='}

session = requests.session()

for cookie in cookie_dict:
    session.cookies.set(cookie, cookie_dict[cookie])

r = session.get(url, headers = headers).json()

print(r)