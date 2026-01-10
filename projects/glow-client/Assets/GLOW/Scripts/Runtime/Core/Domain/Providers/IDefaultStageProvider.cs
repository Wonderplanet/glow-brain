using GLOW.Core.Domain.Models;

namespace GLOW.Core.Domain.Providers
{
    public interface IDefaultStageProvider
    {
        MstStageModel GetDefaultStage();
    }
}