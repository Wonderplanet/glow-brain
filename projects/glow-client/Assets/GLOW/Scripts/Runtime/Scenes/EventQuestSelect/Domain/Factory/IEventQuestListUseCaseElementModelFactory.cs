using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.EventQuestSelect.Domain.Factory
{
    public interface IEventQuestListUseCaseElementModelFactory
    {
        IReadOnlyList<EventQuestListUseCaseElementModel> Create(MasterDataId mstEventId);
    }
}
