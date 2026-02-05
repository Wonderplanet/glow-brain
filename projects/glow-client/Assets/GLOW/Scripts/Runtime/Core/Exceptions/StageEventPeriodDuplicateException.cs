using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class StageEventPeriodDuplicateException : WrappedServerErrorException
    {
        public StageEventPeriodDuplicateException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
