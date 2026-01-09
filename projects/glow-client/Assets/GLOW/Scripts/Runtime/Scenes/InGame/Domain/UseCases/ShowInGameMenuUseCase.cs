using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Modules.GameOption.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.UseCases
{
    public class ShowInGameMenuUseCase
    {
        [Inject] IUserPropertyRepository UserPropertyRepository { get; }
        [Inject] IInGameScene InGameScene { get; }

        public InGameMenuModel GetInGameMenu()
        {
            var userProperty = UserPropertyRepository.Get();

            var isTwoRowDeck = userProperty.IsTwoRowDeck;
            var isPvp = InGameScene.Type == InGameType.Pvp;

            return new InGameMenuModel(
                userProperty.IsBgmMute,
                userProperty.IsSeMute,
                userProperty.SpecialAttackCutInPlayType,
                new TwoRowDeckModeFlag(isTwoRowDeck),
                userProperty.IsDamageDisplay,
                InGameScene.MstInGame.InGameConsumptionType,
                new CanGiveUpFlag(isPvp),
                new InGameTypePvpFlag(isPvp));
        }
    }
}

