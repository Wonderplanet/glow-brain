using GLOW.Core.Domain.Models;

namespace GLOW.Scenes.EventQuestSelect.Domain.Factory
{
    public interface IReleaseRequiredMstQuestFactory
    {
        MstQuestModel Create(MstQuestModel targetMstQuestModel);
    }
}