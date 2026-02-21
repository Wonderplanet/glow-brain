using System.Collections.Generic;
using GLOW.Core.Domain.Models;

namespace GLOW.Scenes.EventQuestTop.Domain.UseCases
{
    public interface IUnitListToMstCharacterModelFactory
    {
        IReadOnlyList<MstCharacterModel> CreateFromCurrentParty();
    }
}