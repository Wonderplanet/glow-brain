using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class AvatarNotFoundException : DataInconsistencyServerErrorException
    {
        public AvatarNotFoundException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
