using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class BoxGachaPeriodOutsideException : WrappedServerErrorException
    {
        public BoxGachaPeriodOutsideException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}