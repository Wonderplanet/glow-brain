using GLOW.Core.Domain.Models.Pass;

namespace GLOW.Core.Domain.Repositories
{
    public interface IHeldPassEffectRepository
    {
        void SetHeldPassEffectModels(HeldPassEffectListModel model);

        HeldPassEffectListModel GetHeldPassEffectListModel();
    }
}