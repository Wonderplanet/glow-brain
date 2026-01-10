using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class UnitNotFoundException : DataInconsistencyServerErrorException
    {
        public UnitNotFoundException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
