using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.UnitDetail.Presentation.Views;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.UnitDetailModal.Presentation.Views
{
    // 全画面(モーダル)表示する用のUnitDetailView
    public class UnitDetailModalViewController : UnitDetailViewController, IEscapeResponder
    {
        public record Argument(MasterDataId MstUnitId, MaxStatusFlag IsMaxStatus);

        [Inject] IUnitDetailModalViewDelegate VIewDelegate { get; }

        public override void LoadView()
        {
            PrefabName = "UnitDetailModalView";
            base.LoadView();
        }
        
        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden) return false;

            SoundEffectPlayer.Play(SoundEffectId.SSE_000_003);
            VIewDelegate.OnCloseButtonTapped();
            return true;
        }

        [UIAction]
        void OnModalCloseButton()
        {
            VIewDelegate.OnCloseButtonTapped();
        }
    }
}
