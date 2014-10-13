#include <stdio.h>
#include <stdlib.h>
#include <stdarg.h>

static void updateArgs( int *pargc, char **argv, int rm_cnt )
{
	int i ;
	/* update the argument count */
	(*pargc)-- ;
	/* update the command line */
	for( i = rm_cnt ; i < *pargc ; i++ ) argv[i] = argv[i+1] ;
}

int pc_flagarg(int *argc, char **argv, char *flag) {

	int i;

	for(i = 1; i < *argc; i++){
		if (!strcmp(argv[i], flag)) {
			updateArgs(argc, argv, i);
			return(1);
		}
	}
	return(0);
}

char *pc_stringarg(int *argc, char **argv, char *flag, char *value) {

	int i;

	for(i = 1; i < *argc -1; i++){
		if (!strcmp(argv[i], flag)) {
			value = argv[i+1];
			updateArgs(argc, argv, i+1);
			updateArgs(argc, argv, i);
			return(value);
		}
	}
	return(value);
}

int pc_intarg(int *argc, char **argv, char *flag, int value) {

	int i;

	for(i = 1; i < *argc - 1; i++){
		if (!strcmp(argv[i], flag)) {
			value = atoi(argv[i+1]);
			updateArgs(argc, argv, i+1);
			updateArgs(argc, argv, i);
			return(value);
		}
	}
	return(value);
}

double pc_doublearg(int *argc, char **argv, char *flag, double value) {

	int i;

	for(i = 1; i < *argc -1; i++){
		if (!strcmp(argv[i], flag)) {
			value = atof(argv[i+1]);
			updateArgs(argc, argv, i+1);
			updateArgs(argc, argv, i);
			return(value);
		}
	}
	return(value);
}

