using Cysharp.Threading.Tasks;
using GLOW.Modules.InvertMaskView.Presentation.ValueObject;
using GLOW.Scenes.Home.Domain.Constants;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.Home.Presentation.Views
{
    public interface IHomeViewDelegate
    {
        void OnViewDidLoad();
        void OnViewWillAppear();
        void OnViewDidUnload();

        void ShowTapBlock(bool shouldShowGrayScale, RectTransform invertMaskRect, float duration);
        void HideTapBlock(bool shouldShowGrayScale, float duration);
        void OnBackTitle();
        void SetBackKeyEnabled(bool isEnabled);


        //ヘッダー
        void OnDiamondSelected();
        void OnAvatarSelected();
        void OnEmblemSelected();

        //フッター
        void OnContentSelected(HomeContentTypes contentType);
        void OnGachaButtonTapped();
        void OnShopButtonTapped();
        void OnProfileSelected();

    }
}
