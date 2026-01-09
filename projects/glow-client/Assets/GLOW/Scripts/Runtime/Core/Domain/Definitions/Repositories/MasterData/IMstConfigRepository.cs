using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMstConfigRepository
    {
        MstConfigModel GetConfig(MstConfigKey key);
    }
}
