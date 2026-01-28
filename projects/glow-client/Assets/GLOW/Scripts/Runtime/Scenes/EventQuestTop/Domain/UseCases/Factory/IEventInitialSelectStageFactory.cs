using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.EventQuestTop.Domain.Models;

namespace GLOW.Scenes.EventQuestTop.Domain.UseCases
{
    public interface IEventInitialSelectStageFactory
    {
        MasterDataId Create(
            MasterDataId mstQuestGroupId,
            IReadOnlyList<EventQuestTopElementModel> models,
            ShowStageReleaseAnimation showStageReleaseAnimation);
    }
}