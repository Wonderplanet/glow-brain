using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.UseCases
{
    public class DebugGetCharacterUnits
    {
        [Inject] IInGameScene InGameScene { get; }

        public IReadOnlyList<CharacterUnitModel> GetCharacterUnits()
        {
            return InGameScene.CharacterUnits;
        }
    }
}
