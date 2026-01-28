using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class PlayerNameOverByteException : WrappedServerErrorException
    {
        public PlayerNameOverByteException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
