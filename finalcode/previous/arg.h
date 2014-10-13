static void updateArgs( int *pargc, char **argv, int rm_cnt );
int pc_flagarg(int *argc, char **argv, char *flag);
char *pc_stringarg(int *argc, char **argv, char *flag, char *value);
int pc_intarg(int *argc, char **argv, char *flag, int value);
double pc_doublearg(int *argc, char **argv, char *flag, double value);
