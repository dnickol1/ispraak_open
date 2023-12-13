# Software Architecture Overview

![Software Architecture](/images/architecture.png)

iSpraak is a tool designed for educational purposes, focusing on automated speech evaluation to assist language learners with immediate feedback. Its primary users are students and educators. Educators have the ability to create reading activities for their students, with three distinct options for delivering the text: 1) Utilizing a synthetic text-to-speech (TTS) voice, 2) Uploading a custom MP3 audio file, or 3) Recording a new audio prompt directly within the application.

When an educator finalizes and submits an activity, iSpraak generates two unique URLs. The first URL is for the educator, allowing them to view all participating students' scores and progress. The second URL is distributed to students, providing them access to undertake the specified activity.

Moreover, iSpraak enables educators to monitor and assess each student's progress and academic performance. This is facilitated through the integration of the JPGraph library, which offers graphical representations of student progress and grade analytics.

The application's architecture comprises a frontend developed in JavaScript, a backend powered by PHP, and a MySQL database for data management and storage. This structure ensures a seamless and interactive user experience while maintaining robust data handling and processing capabilities.

# Development Priorities
* Integrating iSpraak with Learning Management Systems (LMS) using the LTI 1.3 Protocol.
* Enhancing the frontend design of iSpraak for improved user-friendliness and aesthetic appeal.