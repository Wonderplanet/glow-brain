using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class MstNotFoundException : DataInconsistencyServerErrorException
    {
        public MstNotFoundException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
