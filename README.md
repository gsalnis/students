Instaliavimo instrukcijos:
- įdiegti xampp
- įkelti pateiktą marks.sql failą į duomenų bazę
- parsisiųsti students projektą ir įkelti į xampp direktorijoje esantį htdocs aplanką
- konsolėje paleisti komandą 'composer install'
- naršyklėje užeiti į http://localhost/students/public

Atliekant užduotį sužinojau, kurioje vietoje reikia tobulėti, ko trūksta, o ką jau moku.
Nors ir dirbu su Symfony, tačiau atliekant šią užduotį sužinojau, kad nuo nulio sukurti projektą visgi nėra taip paprasta.
Užtruko šiek tiek laiko, kol pavyko iš duomenų bazės pasiimti duomenis, problema buvo ne užklausų rašyme, o pačiame projekto susiejime su duomenų baze.
Iš pradžių bandžiau metodus kelti į atskirus failus, tačiau bandant prisijungti prie duomenų bazės, gaudavau klaidą:
Call to a member function has() on null “Symfony Php”, kadangi dėl kažkokių priežasčių $this->container->has('doctrine'), kuris yra getDoctrine() metode,
nerasdavo doctrine konteinerio.

Kaip minėjau, dirbu su Symfony, tačiau projekte, prie kurio dirbu, yra parašyta struktūra, viskas susieta, belieka kurti naują funkcionalumą.
SQL užklausos apsirašo Repositorijose, Provideriai kviesdami Repositorijose aprašytus metodus gauna reikiamą informaciją, o Providerių metodai naudojami
įvairiuose ataiskaitų Bundles. Kiekvienas Bundle turi savo Provider, savo Repositoriją, jie aprašomi atskiruose services(xml) failuose.
Todėl atliekant šią užduotį trūko praktikos ir žinių, kaip sukurti projektą nuo nulio.

Taip pat dirbu su Docker, žinau kaip jie veikia, kaip sukurti, trinti, paleisti, stabdyti, įeiti, tačiau kaip jį paruošti nuo nulio taipogi nežinau.

Kadangi su frontend dirbti visiškai netenka, Javascript atliktoje užduotyje naudojamas nebuvo.

Dokumentacija:
- getStudentsInfo - metodas, kuriame aprašyta užklausa grąžina informaciją apie studentus ir universitetus, kuriuose studijuoja.
- getStudentMarks(int $studentId) - metodas, kuriame aprašyta užklausa grąžina pateikto studento pažymius ir studijuojamus dalykus.
- getAllSubjects - metodas, kuriame aprašyta užklausa grąžina visus dėstomus dalykus.

- getStudentsMarksAverage() - pagrindinis metodas, kuris apskaičiuoja visų studentų studijuojamų dalykų vidurkius ir pateikia duomenis į pusalpį.
- getStudentMarksAverageBySubject() - suskaičiuoja pateiktų pažymių vidurkį ir sugrupuoja pagal studijuojamą dalyką.
- multiArraySearch() - metodas, kurio pagalba galima ieškoti reikšmių dviejų sluoksnių sąrašuose(array).
- normalizeData(array $dataToNormalize, array $students) - metodas, kuriuo yra tvarkingai sudėliojami duomenys.

Dėl testavimo:
Padariau apsaugą, kad jeigu nutinka taip, kad atsiranda naujas subject, ir tam tikri studentai neturi to dalyko pažyio, būtų parašyta:
'Studentas neturi nė vieno pažymio šiame dalyke'
Tačiau, kadangi trūksta patirties su fronted dalimi, man nepavyko padaryti dinaminės lentelės, todėl kaskart pridėjus naują subject,
 reikia pridėti papildomą parametrą index.html.twig, pvz: <td>{{ data.5 }}</td>
