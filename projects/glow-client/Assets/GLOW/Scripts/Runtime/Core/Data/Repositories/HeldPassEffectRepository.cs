using GLOW.Core.Domain.Models.Pass;
using GLOW.Core.Domain.Repositories;

namespace GLOW.Core.Data.Repositories
{
    public class HeldPassEffectRepository : IHeldPassEffectRepository
    {
        HeldPassEffectListModel _heldPassEffectListModel = HeldPassEffectListModel.Empty;
        
        void IHeldPassEffectRepository.SetHeldPassEffectModels(HeldPassEffectListModel model)
        {
            _heldPassEffectListModel = model;
        }

        HeldPassEffectListModel IHeldPassEffectRepository.GetHeldPassEffectListModel()
        {
            return _heldPassEffectListModel;
        }
    }
}