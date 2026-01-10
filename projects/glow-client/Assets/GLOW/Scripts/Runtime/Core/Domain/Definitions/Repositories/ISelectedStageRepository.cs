using GLOW.Core.Domain.Models;

namespace GLOW.Core.Domain.Repositories
{
    public interface ISelectedStageRepository
    {
        void Save(SelectedStageModel selectedStageModel);
        SelectedStageModel Get();
    }
}
