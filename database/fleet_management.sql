--
-- PostgreSQL database dump
--

\restrict di8yphP87xxciVNzCNv8mOVRX7qlhp1K5TjJ2GrX2cILBpS4cqrncEaqwJOMqTV

-- Dumped from database version 18.4
-- Dumped by pg_dump version 18.4

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: pgcrypto; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS pgcrypto WITH SCHEMA public;


--
-- Name: EXTENSION pgcrypto; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION pgcrypto IS 'cryptographic functions';


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: alerts; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.alerts (
    id integer NOT NULL,
    title character varying(255) NOT NULL,
    detail character varying(255) NOT NULL,
    icon character varying(50) NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: alerts_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.alerts ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.alerts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: drivers; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.drivers (
    id integer NOT NULL,
    name character varying(100) NOT NULL,
    role character varying(100) NOT NULL,
    score numeric(3,1) NOT NULL,
    dispatch_count integer NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: drivers_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.drivers ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.drivers_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: reservations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.reservations (
    id integer NOT NULL,
    driver_name character varying(100) NOT NULL,
    vehicle_type character varying(100) NOT NULL,
    reservation_date date NOT NULL,
    duration_days integer NOT NULL,
    status character varying(50) NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: reservations_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.reservations ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.reservations_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


--
-- Name: users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.users (
    id integer NOT NULL,
    name character varying(100) NOT NULL,
    email character varying(100) NOT NULL,
    password character varying(255) NOT NULL,
    role character varying(50) NOT NULL,
    status character varying(50) NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.users ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: vehicles; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.vehicles (
    id integer NOT NULL,
    name character varying(100) NOT NULL,
    status character varying(50) NOT NULL,
    type character varying(100) NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: vehicles_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.vehicles ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.vehicles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Data for Name: alerts; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.alerts (id, title, detail, icon, created_at) FROM stdin;
1	Scheduled Vios Maintenance due on Aug 02	Service bay 3 • 09:00	🛠️	2026-08-19 14:37:01.799301
2	Route deviation detected on North Loop	Driver Harvey Villarin • 11 mins ago	🗺️	2026-08-19 14:37:01.799301
3	Accident report logged for Unit #A17	Insurance follow-up pending	⚠️	2026-08-19 14:37:01.799301
4	Fuel spike noted on Cargo Unit #C08	Consumption above threshold	⛽	2026-08-19 14:37:01.799301
\.


--
-- Data for Name: drivers; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.drivers (id, name, role, score, dispatch_count, created_at) FROM stdin;
1	Joanna Reforsado	Senior Driver	9.8	124	2026-08-19 14:37:01.799301
2	Daniella Agus	Regional Courier	9.4	111	2026-08-19 14:37:01.799301
3	Erwin Cober	Executive Driver	9.2	98	2026-08-19 14:37:01.799301
\.


--
-- Data for Name: reservations; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.reservations (id, driver_name, vehicle_type, reservation_date, duration_days, status, created_at) FROM stdin;
1	Joanna Reforsado	SUV Cargo	2026-08-03	4	pending	2026-08-19 14:37:01.799301
2	Daniella Agus	Van Shuttle	2026-08-05	2	approved	2026-08-19 14:37:01.799301
3	Erwin Cober	Executive Sedan	2026-08-07	1	pending	2026-08-19 14:37:01.799301
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
LrrlBeX8eEcVwWdtBeIysTIOOe5K5lHmQRPFgfgg	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.131.0 Chrome/148.0.7778.280 Electron/42.7.0 Safari/537.36	eyJfdG9rZW4iOiJLSm1pNzdGZHpOdHlyVDVRWUxzekFubkVyUVpTMUw3NnpBT0taQk5sIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL3JlZ2lzdGVyIiwicm91dGUiOiJyZWdpc3RlciJ9fQ==	1787123249
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.users (id, name, email, password, role, status, created_at) FROM stdin;
1	Reybie Ruelo	reybie@fleetops.com	$2y$12$dku67uwsibfg2ETNfSMZJOvigud/RXnXSaFsIRSpFN05eV8rFeu1.	Admin	active	2026-08-19 14:38:17.190781
2	Maria Santos	maria@fleetops.com	$2y$12$JxSbuqBeB1zN7XUTV6c5D.ztyMDGRA5SBE7DlflbwnXM76YKXc26O	Manager	active	2026-08-19 14:38:17.190781
3	Juan dela Cruz	juan@fleetops.com	$2y$12$zX06SklBAVnJiZlVbwchqe1/On1UgKUCppXaFl6XW8aNt9TLRnVrq	Dispatcher	active	2026-08-19 14:38:17.190781
4	Ana Garcia	ana@fleetops.com	$2y$12$0mQPFWPshmL0v.6NGhqH8.2VZV7L2hm/rsQC7ANlzDNK9DmrzaHTW	Accountant	active	2026-08-19 14:38:17.190781
5	Pedro Reyes	pedro@fleetops.com	$2y$12$.z./2QuujGuhzlswkdLSOuMlShDsh/NRjs2vpJ2XPHoDk/ghCLaKy	Admin	inactive	2026-08-19 14:38:17.190781
\.


--
-- Data for Name: vehicles; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.vehicles (id, name, status, type, created_at) FROM stdin;
1	SUV Cargo 01	Active	SUV Cargo	2026-08-19 14:37:01.799301
2	Van Shuttle 02	Active	Van Shuttle	2026-08-19 14:37:01.799301
3	Executive Sedan 03	Maintenance	Executive Sedan	2026-08-19 14:37:01.799301
4	Cargo Truck 04	Active	Cargo Truck	2026-08-19 14:37:01.799301
\.


--
-- Name: alerts_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.alerts_id_seq', 4, true);


--
-- Name: drivers_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.drivers_id_seq', 3, true);


--
-- Name: reservations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.reservations_id_seq', 3, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.users_id_seq', 5, true);


--
-- Name: vehicles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.vehicles_id_seq', 4, true);


--
-- Name: alerts alerts_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.alerts
    ADD CONSTRAINT alerts_pkey PRIMARY KEY (id);


--
-- Name: drivers drivers_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.drivers
    ADD CONSTRAINT drivers_pkey PRIMARY KEY (id);


--
-- Name: reservations reservations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.reservations
    ADD CONSTRAINT reservations_pkey PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: users users_email_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_key UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: vehicles vehicles_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vehicles
    ADD CONSTRAINT vehicles_pkey PRIMARY KEY (id);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- PostgreSQL database dump complete
--

\unrestrict di8yphP87xxciVNzCNv8mOVRX7qlhp1K5TjJ2GrX2cILBpS4cqrncEaqwJOMqTV

