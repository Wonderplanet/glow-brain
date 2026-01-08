using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class QuestPeriodOutsideException : DataInconsistencyServerErrorException
    {
        public QuestPeriodOutsideException(int serverErrorCode) : base(
            new ServerErrorException(
                "Quest period outside",
                serverErrorCode,
                string.Empty,
                HTTPStatusCodes.ApplicationError,
                null))
        {
        }

        public QuestPeriodOutsideException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
