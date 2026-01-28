using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class PlayerNameSpaceFirstException : WrappedServerErrorException
    {
        public PlayerNameSpaceFirstException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
