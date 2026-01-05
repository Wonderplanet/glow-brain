using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class PvpPeriodOutsideException : DataInconsistencyServerErrorException
    {
        public PvpPeriodOutsideException(int serverErrorCode) : base(
            new ServerErrorException(
                "Pvp period outside",
                serverErrorCode,
                string.Empty,
                HTTPStatusCodes.ApplicationError,
                null))
        {
        }

        public PvpPeriodOutsideException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
