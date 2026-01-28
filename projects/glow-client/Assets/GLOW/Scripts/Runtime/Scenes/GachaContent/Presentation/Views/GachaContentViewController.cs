using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Scenes.GachaContent.Presentation.ViewModels;
using GLOW.Scenes.Home.Presentation.Views;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.GachaContent.Presentation.Views
{
    /// <summary>
    /// 71-1_ガシャ
    /// 　71-1-1_ガシャトップ
    /// </summary>
    public class GachaContentViewController : HomeBaseViewController<GachaContentView>
    {
        public record Argument(MasterDataId OprGachaId);

        [Inject] IGachaContentViewDelegate ViewDelegate { get; }

        MasterDataId _oprGachaId;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
        }


        public override void ViewDidUnload()
        {
            base.ViewDidUnload();
            ViewDelegate.OnViewDidUnLoad();
        }

        public void UpdateView()
        {
            ViewDelegate.UpdateView();
        }

        public void SetViewModel(GachaContentViewModel viewModel)
        {
            _oprGachaId = viewModel.OprGachaId;
            //アセットロード

            // 画面の設定
            // ActualView.SetViewModel(viewModel);
        }


        #region [UIAction]その他ボタン
        [UIAction]
        public void OnGachaDetailButtonTapped()
        {
            // ガチャ詳細ボタンタップ時
            ViewDelegate.OnGachaDetailButtonTapped(_oprGachaId);
        }


        [UIAction]
        public void OnSpecificCommerceButtonTapped()
        {
            // 特商法ボタンタップ時
            ViewDelegate.OnSpecificCommerceButtonTapped();
        }
        #endregion

    }
}
