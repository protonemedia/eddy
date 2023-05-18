<?php

namespace App;

/**
 * @see \Psy\Readline\Hoa\ConsoleProcessus
 */
enum Signal: string
{
    /**
     * Signal: terminal line hangup (terminate process).
     */
    case HUP = 'HUP';

    /**
     * Signal: interrupt program (terminate process).
     */
    case INT = 'INT';

    /**
     * Signal: quit program (create core image).
     */
    case QUIT = 'QUIT';

    /**
     * Signal: illegal instruction (create core image).
     */
    case ILL = 'ILL';

    /**
     * Signal: trace trap (create core image).
     */
    case TRAP = 'TRAP';

    /**
     * Signal: abort program, formerly SIGIOT (create core image).
     */
    case ABRT = 'ABRT';

    /**
     * Signal: emulate instruction executed (create core image).
     */
    case EMT = 'EMT';

    /**
     * Signal: floating-point exception (create core image).
     */
    case FPE = 'FPE';

    /**
     * Signal: kill program (terminate process).
     */
    case KILL = 'KILL';

    /**
     * Signal: bus error.
     */
    case BUS = 'BUS';

    /**
     * Signal: segmentation violation (create core image).
     */
    case SEGV = 'SEGV';

    /**
     * Signal: non-existent system call invoked (create core image).
     */
    case SYS = 'SYS';

    /**
     * Signal: write on a pipe with no reader (terminate process).
     */
    case PIPE = 'PIPE';

    /**
     * Signal: real-time timer expired (terminate process).
     */
    case ALRM = 'ALRM';

    /**
     * Signal: software termination signal (terminate process).
     */
    case TERM = 'TERM';

    /**
     * Signal: urgent condition present on socket (discard signal).
     */
    case URG = 'URG';

    /**
     * Signal: stop, cannot be caught or ignored  (stop proces).
     */
    case STOP = 'STOP';

    /**
     * Signal: stop signal generated from keyboard (stop process).
     */
    case TSTP = 'TSTP';

    /**
     * Signal: continue after stop (discard signal).
     */
    case CONT = 'CONT';

    /**
     * Signal: child status has changed (discard signal).
     */
    case CHLD = 'CHLD';

    /**
     * Signal: background read attempted from control terminal (stop process).
     */
    case TTIN = 'TTIN';

    /**
     * Signal: background write attempted to control terminal (stop process).
     */
    case TTOU = 'TTOU';

    /**
     * Signal: I/O is possible on a descriptor, see fcntl(2) (discard signal).
     */
    case IO = 'IO';

    /**
     * Signal: cpu time limit exceeded, see setrlimit(2) (terminate process).
     */
    case XCPU = 'XCPU';

    /**
     * Signal: file size limit exceeded, see setrlimit(2) (terminate process).
     */
    case XFSZ = 'XFSZ';

    /**
     * Signal: virtual time alarm, see setitimer(2) (terminate process).
     */
    case VTALRM = 'VTALRM';

    /**
     * Signal: profiling timer alarm, see setitimer(2) (terminate process).
     */
    case PROF = 'PROF';

    /**
     * Signal: Window size change (discard signal).
     */
    case WINCH = 'WINCH';

    /**
     * Signal: status request from keyboard (discard signal).
     */
    case INFO = 'INFO';

    /**
     * Signal: User defined signal 1 (terminate process).
     */
    case USR1 = 'USR1';

    /**
     * Signal: User defined signal 2 (terminate process).
     */
    case USR2 = 'USR2';
}
