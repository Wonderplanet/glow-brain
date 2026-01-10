using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.EnhanceQuestTop.Domain.Models;

namespace GLOW.Scenes.EnhanceQuestTop.Domain.Factories
{
    public interface IQuestPartyModelFactory
    {
        QuestPartyModel Create(UserPartyCacheModel party, MasterDataId mstQuestId);
    }
}

