using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class ChangeNameCoolTimeException : WrappedServerErrorException
    {
        public ChangeNameCoolTimeException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
