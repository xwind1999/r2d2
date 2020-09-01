from locust import HttpUser, TaskSet, task
import json
import time
import random
import string
from datetime import datetime, timedelta
import os

boxId = '851518'
experienceIds = ['102714', '103152', '106685']

now = datetime.now()
date_from = datetime.strftime(now + timedelta(days = 5), '%Y-%m-%d')
date_to = datetime.strftime(now + timedelta(days = 365), '%Y-%m-%d')

def quickdata_getrangev2(self):
    expected_time = 0.7
    headers = {'content-type': 'application/json'}
    response = self.client.get('/quickdata/GetRangeV2/1/12345',params={
    'boxVersion': boxId,
    'dateFrom': date_from,
    'dateTo': date_from
    },
    headers=headers,
    name = 'QuickData GetRangeV2')
    try:
       assert response.elapsed.total_seconds() < expected_time, 'Request Quickdata GetRangeV2 took %r which is more than %r seconds' % (response.elapsed.total_seconds(), expected_time)
       print ('Response code', response.status_code)
       assert response.status_code == 200
    except AssertionError as err:
       print(err)


def quickdata_getpackage(self):
    expected_time = 0.18
    headers = {'content-type': 'application/json'}
    response = self.client.get(
        '/quickdata/GetPackage/1/12345',params={
            'PackageCode': experienceIds[0],
            'dateFrom': date_from,
            'dateTo': date_to
        },
        headers=headers,
        name='QuickData GetPackage'
    )
    try:
        assert response.elapsed.total_seconds() < expected_time, 'Request Quickdata GetPackage took %r which is more than %r seconds' % (response.elapsed.total_seconds(), expected_time)
        print ('Response code', response.status_code)
        assert response.status_code == 200
    except AssertionError as err:
        print(err)


def quickdata_getpackagev2(self):
    expected_time = 0.4
    headers = {'content-type': 'application/json'}
    response = self.client.get(
        '/quickdata/GetPackageV2/1/12345',params={
            'ListPackageCode': ','.join(experienceIds),
            'dateFrom': date_from,
            'dateTo': date_to
        },
        headers=headers,
        name='QuickData GetPackageV2'
    )
    try:
        assert response.elapsed.total_seconds() < expected_time, 'Request Quickdata GetPackageV2 took %r which is more than %r seconds' % (response.elapsed.total_seconds(), expected_time)
        print ('Response code', response.status_code)
        assert response.status_code == 200
    except AssertionError as err:
        print(err)


def quickdata_availability_price_period(self):
    expected_time = 0.18
    headers = {'content-type': 'application/json'}
    response = self.client.get(
        '/quickdata/availabilitypriceperiod/1/12345',params={
            'ExperienceId': experienceIds[0],
            'prestid': 1,
            'datefrom': date_from,
            'dateto': date_to
        },
        headers=headers,
        name='QuickData AvailabilityPricePeriod'
    )
    try:
        assert response.elapsed.total_seconds() < expected_time, 'Request Quickdata AvailabilityPricePeriod took %r which is more than %r seconds' % (response.elapsed.total_seconds(), expected_time)
        print ('Response code', response.status_code)
        assert response.status_code == 200
    except AssertionError as err:
        print(err)
