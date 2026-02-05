using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class MissionCannotReceiveOutPeriodLimitedTermException : DataInconsistencyServerErrorException
    {
        public MissionCannotReceiveOutPeriodLimitedTermException(ServerErrorException serverErrorException) 
            : base(serverErrorException)
        {
        }
    }
}
