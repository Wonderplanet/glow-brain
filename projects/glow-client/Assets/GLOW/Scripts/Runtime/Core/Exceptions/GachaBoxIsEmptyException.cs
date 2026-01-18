using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class GachaBoxIsEmptyException : WrappedServerErrorException
    {
        public GachaBoxIsEmptyException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
