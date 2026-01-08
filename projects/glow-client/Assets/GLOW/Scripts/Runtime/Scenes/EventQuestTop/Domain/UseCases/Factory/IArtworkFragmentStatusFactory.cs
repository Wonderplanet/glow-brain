using System.Collections.Generic;
using GLOW.Core.Domain.Models;

namespace GLOW.Scenes.EventQuestTop.Domain.UseCases
{
    public interface IArtworkFragmentStatusFactory
    {
        ArtworkFragmentStatusModel Create(IReadOnlyList<MstQuestModel> mstQuestModels);
    }
}
