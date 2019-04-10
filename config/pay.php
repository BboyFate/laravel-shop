<?php

return [
    'alipay' => [
        'app_id'         => '2016092500596451',
        'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA7h2iDPtO6s02MKH6saL95RIkrfI1eDNm0/cq2PVmiUpiocuMiiuJYKTaGVtD2bK/io5AFkt36IJ/PltZbE+LGRCpBAqqbA8oPNN9YO39bDtl6zrTbk53/RqzssjzK9AbtQ5Z8LkBb0PMMgRDREKlPkLSep1tZoYtrgyvwxxC23156fetJ0an5B+TKdiVKIYpMqw5VZnlkVICDZakXw8F2yqGw9rx2z0It/jIT5lDc6lTWlDbaLtFpCElK16KkSWV/ZlNFkJlDO+E+3gw6Yxnwmkq//fSfZaWo4Tt78chp09NyOPz49l4ZrRIRn6j9zolvzYLj8WUF6euptlPzsYfTQIDAQAB',
        'private_key'    => 'MIIEowIBAAKCAQEApAJ1E0+ufD59f//nzqCT/fQIm4WOdghAtkgvvScmZe6aueTWxa2X0KzVrDYi43tMa9ZaTU/8BOlSo/x9iRfM8ClD9lkitYSX3v4HRusL+iJ5NjqDBZ+0dhDeCkKRhEz1AWo0hfqdHv2E9pLjQ99t3/Td26LQUEaLT11q3Z1pqyC40YmDgFD45WdNvmC74QbkF8jVsglPmSakBO1Z6S3jicQXpvuiv6ue+U1NBnwrbfuRZXOUq1m3/grvssSmBU0iZkHNfKP9JN/z1u2brwE1ajKZ73ZGVghcmXGathtLyHslXqiYgMA894OfsOtkWisUdapHAVTC+DMiTLp+qc3PLwIDAQABAoIBADFguATHYZ3wZnJqBk1UVRoJJapFDH4GwAzhMNY++jWIH7dF6U98kw91aaM+bUSVs41n7KoIIbUM1h+e/eCPdNj9Y4PAlE2YDO1LB7/XNjTpuhqUhSvlSDyfmWn3Xr45OS5i3ktUB4f0BdsCoD+n/fmGTGE65IK2qmyLkIgGQ3EMnnGssfVlYLOl7MzQjMJA6I3xS8qGjvPsj03B1BB2+DuOnVmeSWrbPnbXrCEKT9027pwgKE0p9DMTVQPiu4PsAx93TSsrs/bWZj3/Rluc6TnVY7ldhCBKrQEwdv0pNB9jFS40qmky8VRIP1Hw0moBItN2bTAH/b1rm8PyEgAadkkCgYEA1v+IsAkINaFnUlKqaAufs8axl6XrXdcSsLWuxfvwiea4BothUgQZKQVYQm2ZUXvgT+RzMgjSTHUtOedW8BnA+xyUpiaf2ujntUCrHPctrwEWUbmNmMKQ6UwEfd+57YsPyw2JcxsJeugwlhIT1xB9GWjmYuNAnAbGibvhwYoc+ZsCgYEAw0mZtZxFKuSGfvkAA+Z0te4fiVqLF7pt5lS3zQy3If7RW79El6LzbZVRwfGWTZFVD0C95fwQEBcFVUZC8QKv6R7oKpzH4NyW4fuVfQHDthG7buC3FUVS3xVbdwqCaReH95pkBQ26q2P2avCXr9Cyu0W7pNJouRHRKLFCU3UH8/0CgYBly7u2TbB7ZB59lfJlLQvCnRM9ECwu1ERfMsa7qxwqzvjW8Gt138bh60vst+Pd9t8wNKRuTkr0NI7jWauKJ0d+Hfwr5yHNDnWXFjVuk4In2EXvEAIBEC54p6uAxctMs4kYxSINRAHrFcDbbVfDfmUdANQEgkD3HyYl1LWBY5lwfQKBgG/qYBJ6KhF4wbNGk8Gocr5IZv5aS1gf2Rek+Wijntf2ppiDtpWIJyVn62DQSKjPXwXMvodsuu/doYrLJ1Xjilh5A+hXzRyKjlWXzoXDWxueceILGiEwh1uda7t+5FnByIQXkLZstcu/D9sdYnC5k5RLVEwS4T0eftVpMG+PpC0pAoGBAIaFoS0XF5hcUyD5c1uWvS0vDT2XMq8HIN9aO6/XIPc4yHBysvUMpG7NsOjOhIYNtR19RZDYLOFnLgUXn7HByowqgT4r37SJHKkMONJiAThctgDN9XRz+0C9yorB14qDWamKtU4y14DGbWdcql68DJKK2oRtIVBjlNisiy90Xz74',
        'log'            => [
            'file' => storage_path('logs/alipay.log'),
        ],
    ],

    'wechat' => [
        'app_id'      => '',
        'mch_id'      => '',
        'key'         => '',
        'cert_client' => '',
        'cert_key'    => '',
        'log'         => [
            'file' => storage_path('logs/wechat_pay.log'),
        ],
    ],
];
