## compress regular
1. remove comments
2. remove newline
3. combine padding & margin value
4. override the same properties(but not background, background-image, background-color)
5. sort properties with hack properties
6. sort selectors
7. combine the same properties in two selectors.
8. replace multi blank chars to one
9. replace 0px to 0
10. replace long color to short one
11. remove useless css class
12. replace 0.6 to .6
13. rgb(0,0,0) to #000

## notice
1. if property is filter, can not relace `, ` to `,`
2. if selector has hack property, can not combine the same properties
3. can not override the same properties when property is background, background-image, background-color
4. sort selectors has been aborted when one selecotor has ,.
5. if has value hack(such as: \9), can't not override the same properties
6. the same selector in different place, can't merge them if there are the same Specificity selector between them.