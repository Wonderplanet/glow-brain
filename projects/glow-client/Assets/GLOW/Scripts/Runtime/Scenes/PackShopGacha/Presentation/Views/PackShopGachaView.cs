using Cysharp.Threading.Tasks;
using DG.Tweening;
using UIKit;
using UnityEngine;
using UnityEngine.UI;
using WonderPlanet.UniTaskSupporter;

namespace GLOW.Scenes.PackShopGacha.Presentation.Views
{
    public class PackShopGachaView : UIView
    {
        [SerializeField] UICollectionView _gachaBannerListView;
        [SerializeField] ScrollRect _scrollRect;
        [SerializeField] CanvasGroup _canvasGroup;
        public UICollectionView GachaBannerList => _gachaBannerListView;
        public ScrollRect ScrollRect => _scrollRect;
        
        public void MoveScrollToTargetPos(float targetPos)
        {
            DoAsync.Invoke(this, async ct =>
            {
                // キャンバスのアルファを0にする
                _canvasGroup.alpha = 0f;
                
                // 画面遷移時にスクロール位置を調整するため1フレーム待つ
                await UniTask.Delay(1, cancellationToken: ct);
                
                _scrollRect.verticalNormalizedPosition = targetPos;
                // 表示が完了したのでアルファを1にする
                _canvasGroup.DOFade(1f, 0.15f).Play();
            });
        }
    }
}