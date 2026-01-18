using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class StageEventPeriodOutsideException : DataInconsistencyServerErrorException
    {
        public StageEventPeriodOutsideException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
