using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class StageContinueLimitException : DataInconsistencyServerErrorException
    {
        public StageContinueLimitException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
