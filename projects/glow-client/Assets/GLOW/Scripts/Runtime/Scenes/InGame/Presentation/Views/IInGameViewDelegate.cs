using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UIKit;

namespace GLOW.Scenes.InGame.Presentation.Views
{
    public interface IInGameViewDelegate
    {
        void OnViewDidLoad();
        void OnViewDidUnload();
        void OnCharacterSummonButtonTapped(MasterDataId characterId);
        void OnUseSpecialAttackButtonTapped(MasterDataId characterId);
        void OnSpecialUnitSummonButtonTapped(MasterDataId characterId);
        void OnUnitDetailLongPress(UserDataId userUnitId);
        void TransitToHome();
        void OnRushButtonTapped();
        void OnBattleSpeedButtonTapped();
        void OnAutoButtonTapped();
        bool SelectSpecialUnitSummonTarget(MasterDataId characterId, PageCoordV2 pos);
        void OnMenuButtonTapped();
        void OnSkipButtonTapped();
        void OnEscapeButtonTapped();
        bool IsPlayingBattle();
        void PresentModally(UIViewController controller, bool animated = true, Action completion = null);
    }
}
