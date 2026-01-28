using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class MissionCannotReceiveOutPeriodEventException : DataInconsistencyServerErrorException
    {
        public MissionCannotReceiveOutPeriodEventException(ServerErrorException serverErrorException) 
            : base(serverErrorException)
        {
        }
    }
}
