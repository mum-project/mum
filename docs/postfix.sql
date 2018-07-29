##
## Postfix
##

## mailboxes
SELECT 1 AS found
FROM mailboxes
    INNER JOIN domains ON mailboxes.domain_id = domains.id
WHERE
    mailboxes.local_part = '%u'
    AND domains.domain = '%d'
    AND domains.active = 1
    AND mailboxes.active = 1
LIMIT 1;

## aliases
SELECT GROUP_CONCAT(DISTINCT alias_recipients.recipient_address SEPARATOR ',')
FROM aliases
    INNER JOIN alias_recipients ON aliases.id = alias_recipients.alias_id
    INNER JOIN domains ON aliases.domain_id = domains.id
WHERE
    aliases.local_part = '%u'
    AND domains.domain = '%d'
    AND domains.active = 1;

## domains
SELECT domain
FROM domains
WHERE
    domain = '%s'
    AND active = 1;

## recipient-access
SELECT IF(send_only = 1, 'REJECT', 'OK') AS access
FROM mailboxes
    INNER JOIN domains ON mailboxes.domain_id = domains.id
WHERE
    mailboxes.local_part = '%u'
    AND domains.domain = '%d'
    AND domains.active = 1
    AND mailboxes.active = 1
LIMIT 1;

## sender-login-maps
SELECT CONCAT(mailboxes.local_part, '@', domains.domain) AS owns
FROM mailboxes
    INNER JOIN domains ON mailboxes.domain_id = domains.id
WHERE
    mailboxes.local_part = '%u'
    AND domains.domain = '%d'
    AND domains.active = 1
    AND mailboxes.active = 1
UNION SELECT GROUP_CONCAT(CONCAT(mailboxes.local_part, '@', mailbox_domains.domain) SEPARATOR ',') AS owns
      FROM aliases
          INNER JOIN domains alias_domains ON aliases.domain_id = alias_domains.id
          INNER JOIN alias_senders ON aliases.id = alias_senders.alias_id
          INNER JOIN mailboxes ON alias_senders.mailbox_id = mailboxes.id
          INNER JOIN domains mailbox_domains ON mailboxes.domain_id = mailbox_domains.id
      WHERE
          aliases.local_part = '%u'
          AND alias_domains.domain = '%d'
          AND alias_domains.active = 1
          AND mailbox_domains.active = 1
          AND aliases.active = 1;

## tls-policy
SELECT
    policy,
    params
FROM tls_policies
WHERE
    domain = '%s'
    AND active = 1;