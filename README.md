# humanID: Web Login Integration

## Overview

- humanID act as a Authentication service. **It replaces traditional username password with One Time Password that is sent via SMS**. [See the difference between Authentication and Authorization](https://auth0.com/docs/authorization/concepts/authz-and-authn).
- Partner App have a full control over Authorization. Therefore, Partner App can use existing Authorization process.

## Integration

**Prerequisites**

- **humanID Server Credentials**, a Server Credentials is consist of:
    - Server Client ID
    - Server Client Secret
- **App Back-end**, to receive authentication Callback from humanID 

The authentication process can be illustrated in the following diagram:

![alt text](https://ptuml.hackmd.io/svg/VLNVZzis37ws_WgYB-sC9KMxjw1BOJlENT0q2NhNhS1G1BAjn48ToRNbksL__KZvHwmiDp_upk57wYC_akAfqHLyhKK5l8Q0bRmoCfKbLmR-54bOwUVE5bxRWh8CUVf3g8pCaMFEcnDNgmMYrlee5G6Mjm2HhWGP5uxnuVF3hj9FCcjFZXbZh-7T5Ew4WJRUrozh_6bozJ0Mm6GEuGoYGgO_B3HiZD6ACQMDW48S3EW3vkBPb5E2mQj0QNK-wQQcwB1Iy2nDtbDy1KaRmjCATiXIeICc1fW_c9mRa3LaiYuBVXOP88YCNU4OLOUEPeodHZvn9867XqGpce7l__NkUvkIxpF0Pq15Z7bUHBZb3Bw8duseZGLIwcXoybdY3nq6JWs-4W6PLWBkTMqcHa_eBsACXh92nnHhBAedJ2IgH2QKaRoexVdq39pfmniOBU7MWr6z9IfXy-drVX1rBRMbskCoSGdKfTPAzOBF24-b28TAPB92_e_wXXyRaMKUHLdsfumhk7tSxVXHiF_GildGAxHkmMstaKlOzLKbZxaL8kWQwraM1ObGTNInCR3skJ5bFNlxzbaaau92JcsFJMGsrTKHsMFXhMSjS2gWnDF8JQgoCL3c9AXgJedJLZGQxwUmBONgPcFNkVKDWRnlpChavU5vQ9AJD9qxUh_PqT4RU_8lupuUo-BKoMtbjh3K9NE8CJoInmbteteNwM8sxg_OIh4aLccAKb_rtaYCYz-W-eegDvPxKFXQTy_LtN5q17VEHt9pYyTyc4C5A_xRj1NaKbZx-GR3lb35mTwLSvNz2XOzq1xtjyV6sIyk86_8lrjNmMxLpyru9Fz_0Fe5zVxxQEWimuWNHOAR7sDrMyk-BfDWFw-Xj2OvBMxgpGknTeLIrrilTsRumU0mDJHHTIl_eIcAC-3m7D6C0mJCkb7OVkOEkZfnW-upQ_gmtjwjDlieMA_38FgqH-Zyep2_Vy1_zl4csYxYnJx-5jq7cxju_xZz56_cRL3VZ_zESwwEub7ZDUVxRGg-N-UwCfD2FeaCVpHaR4lGNWSgaodMisw9vtY39G8Hl37e9_z1uicvkrqox65dtLvV0bPFUmqiPl27gEJXx91YWvfNdT3lvaM_csalhHQk-5SDSCCQTOrm9r0kn7JRrjdWz_eaxA2yiBZRaRgWd7dw5m00)

### 1. Create a Log-in Page

Create a Log-in page that contains this button. 

![login-button](https://i.imgur.com/XFSVbnx.png)

Get [Log-in button image](https://web-login.human-id.org/demo/anonymous-login.svg) and put this scripts below into your Web App:

```htmlmixed
<a href="{{REPLACE_WITH_TARGET_URL}}"><img src="anonymous-login.svg" alt="Anonymous Login with humanID" height="27"></a>
```

### 2. Create a Log-in API

When user click the log-in with button, Web App will make request to the App Backend and then page will be redirected to web-login.human-id.org.

To obtain Log-in URL, App Back-end will call **API Request Web Log-in Session** [(See Documentation below)](#API-Request-Web-Log-in-Session). The API call  must be done between Host-to-Host in order to protect Server Credentials.

Once App Backend received response that contains Log-in URL, redirect page to given URL to open **humanID Web Log-in Page**

### 3. Create Log-in Callback API

After user successfully Log-in with humanID, page will be redirected to a registered **Log-in Callback URL**. Callback URL contains **Exchange Token**, which is a URL Encoded token that will be used to obtain User ID.

A Log-in callback URL is formatted:

```
<LOGIN_CALLBACK_URL>?et=<URL_ENCODED_EXCHANGE_TOKEN>
```

For example:

```bash
https://api.filmreview.example.com/humanid-callback?et=9F27%2BOpExCGqTrk6caay66fb%2FumdjAN0LnmTRgxj%2Fq70FplDictSay0lUQvTqkJ6S7agUwbfGN5bhbbJnRbrIpBI1goDa7qBgN88ZjYnDZDI9YrgEV1qlxTNyrGQp79Oc4rCQOemZT162StlEXsiEeAZRAwDJfele%2F6vQszqc2PtlwQ%3D%3D
   ```

URL Decode **Exchange Token** and use it as paramter to call **API Exchange Token** [(See Details)](#API-Exchange-Token) to obtain User ID.

Once App Backend receive response from API call, use given User ID to authorize User so User could access the Web App contents.

**Handle Error Response**

If log-in failed, humanID will redirect to configured **Log-in Callback URL** that formatted:

```
<LOGIN_CALLBACK_URL>?code=<ERROR_CODE>&message=<ERROR_MESSAGE>
```

To check whether log-in failed or not, simply check if parameter `et` appended in callback URL

## API Documentation

### API Request Web Log-in Session

- Endpoint URL
  - `POST https://core.human-id.org/v0.0.3/server/users/web-login`
- Request
  - Headers
    | Key             | Value                    |
    | --------------- | ------------------------ |
    | `client-id`     | `<SERVER_CLIENT_ID>`     |
    | `client-secret` | `<SERVER_CLIENT_SECRET>` |
    | `Content-Type`  | `application/json`       |
  - Query Parameters:
    | Key                | Value | Description    |
    | ------------------ | ----- | --- |
    | `lang`             |       | Language to show    |
    | `priority_country` |       |     |
    
- Response Example
  - Success
    ```json
    {
        "success": true,
        "code": "OK",
        "message": "Success",
        "data": {
            "webLoginUrl": "https://web-login.human-id.org/login?t=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJwdXJwb3NlIjoid2ViLWxvZ2luL3JlcXVlc3QtbG9naW4tb3RwIiwic2lnbmF0dXJlIjoiODNiMDMxNjMwMTkzMjE5ZjMzNWM2MGI0OGU2MGQ5MzVlZWQ5ZDkzNDNlYjRiZmFjYzRlOTFmMTUxOTVhMDVlNyIsImlhdCI6MTU5OTI3MTczNSwiZXhwIjoxNTk5MjcyMDM1LCJzdWIiOiJTRVJWRVJfR1hJVFM3TlZZM0RETVozNVdVSDdDWCIsImp0aSI6InR1SWdOdU1LMjBseGI3a2pGeG9DUFNMeUx2UE8yNkJuWmtmMHc1WjZvTG9PcTlhZkRMblJGSHh0VHVGZllRSGoifQ.CVUA8DYOAk0nbu0_ftTFNMwtfCJ32hCqY_6MKP43Sg8&a=IO5T8PZH2O15N8SV&lang=en"
        }
    }
    ```
  - Error: Invalid Server Credentials
    ```json
    {
        "success": false,
        "code": "401",
        "message": "Unauthorized"
    }
    ```

### API Exchange Token

- Endpoint URL
  - `POST https://core.human-id.org/v0.0.3/server/users/exchange` 
- Request
  - Headers
    | Key             | Value                    |
    | --------------- | ------------------------ |
    | `client-id`     | `<SERVER_CLIENT_ID>`     |
    | `client-secret` | `<SERVER_CLIENT_SECRET>` |
    | `Content-Type`  | `application/json`       |
  - Body
    ```json
    {
      "exchangeToken": "0BYLCicta3dO5DrTkrfQxo7Z4hxmyAh5OwuVPEGS5SlnBGwY+A/t7BNKzGcZFGqGOnI97nGQJ6SGoMf8vyux+D3AYmk63CR9AUnO7f+zlTL4MX9t2OhBdMZoLNP21ucvnTjiR5EIO7qwnFRVN4VquMCUMV8Kmt7N1s6V3yXHmDM="
    }
    ```

- Response Example
  - Success
    ```json
    {
        "success": true,
        "code": "OK",
        "message": "Success",
        "data": {
            "userAppId": "<UNIQUE_USER_ID>",
            "countryCide": "ID"
        }
    }
    ```
    
## Error Codes

| Code   | HTTP Status | Message                                           |
| ------ | ----------- | ------------------------------------------------- |
| ERR_1  | 400         | Invalid exchange token                            |
| ERR_2  | 400         | Exchange token has been expired"                  |
| ERR_3  | 403         | Existing login found                              |
| ERR_4  | 401         | Invalid App Secret                                |
| ERR_5  | 400         | Invalid verification code                         |
| ERR_6  | 401         | User is not granted to current app                |
| ERR_7  | 403         | App has no permission to access user data         |
| ERR_8  | 400         | Requested App is equals to existing User Hash     |
| ERR_9  | 403         | deviceId is not authorized                        |
| ERR_10 | 400         | Invalid phone number input                        |
| ERR_11 | 400         | Invalid OTP Session                               |
| ERR_12 | 400         | Resend has reached limit                          |
| ERR_13 | 400         | Failed attempt has reached limit                  |
| ERR_14 | 400         | Wait for allowed next resend timestamp            |
| ERR_15 | 400         | OTP Session has expired                           |
| ERR_17 | 400         | App not found                                     |
| ERR_18 | 400         | credentialTypeId must be Server (1) or Mobile (2) |
| ERR_19 | 400         | clientId not found for given App                  |
| ERR_26 | 400         | Invalid Web Log-in requester server credential    |
| ERR_27 | 400         | Invalid Web Log-in session signature              |
| ERR_28 | 400         | Redirect URL is not configured in App             |
| ERR_29 | 400         | Invalid App configuration request body            |
| WSDK_01| 200         | The session has expired, please start again       |
| WSDK_02| 200         | Time expired - Please restart verification process|

Example of error format : https://human-id.org/?code=ERR_11&message=Invalid%20OTP%20Session 

## Contribution Guide
- Please reference this guide for additional details about contributing to our project: https://docs.google.com/document/d/1yYo7rb6CRMO14Bg9awxgh6YP--lIotrrC-r3hqAtNTY/edit?usp=sharing

## License
Copyright 2019-2020 Bluenumber Foundation\
Licensed under the GNU General Public License v3.0 [(LICENSE)](https://github.com/human-internet/humanid-weblogin/blob/master/LICENSE)
