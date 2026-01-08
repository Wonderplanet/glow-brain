using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class AdventBattlePeriodOutsideException : DataInconsistencyServerErrorException
    {
        public AdventBattlePeriodOutsideException(int serverErrorCode) : base(
            new ServerErrorException(
                "Advent battle period outside",
                serverErrorCode,
                string.Empty,
                HTTPStatusCodes.ApplicationError,
                null))
        {
        }

        public AdventBattlePeriodOutsideException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
