using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class EventPeriodOutsideException : DataInconsistencyServerErrorException
    {
        public EventPeriodOutsideException(int serverErrorCode) : base(
            new ServerErrorException(
                "Event period outside",
                serverErrorCode,
                string.Empty,
                HTTPStatusCodes.ApplicationError,
                null))
        {
        }

        public EventPeriodOutsideException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
