
# Voidem API

Voidem API is a collection of public custom APIs that are used in some of my projects.


## API Reference

Currently only one endpoint available

#### Get listening data

```http
  GET /v1/listening
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `api_key` | `string` | **Required**. Your API key |



## Environment Variables

To run this project, you will need to add the following environment variables to your .env file

`DB_HOST`

`DB_USER`

`DB_NAME`

`DB_PASS`

`URL_LISTENING`

`RESTRICTED_IPS='["IP HERE", "ANOTHER IP HERE"]'`
## Demo

A demo of the api can be tested by using the api key `demo123`
## Documentation

[Documentation](https://apidocs.voidem.com)

