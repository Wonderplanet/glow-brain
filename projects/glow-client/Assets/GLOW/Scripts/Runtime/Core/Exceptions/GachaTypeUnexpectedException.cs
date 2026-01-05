using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class GachaTypeUnexpectedException : WrappedServerErrorException
    {
        public GachaTypeUnexpectedException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
