using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.EventQuestTop.Domain.Models;

namespace GLOW.Scenes.EventQuestTop.Domain.UseCases
{
    public interface IEventQuestTopUseCaseElementModelFactory
    {
        IReadOnlyList<EventQuestTopElementModel> Create(MasterDataId questGroupId);
    }
}