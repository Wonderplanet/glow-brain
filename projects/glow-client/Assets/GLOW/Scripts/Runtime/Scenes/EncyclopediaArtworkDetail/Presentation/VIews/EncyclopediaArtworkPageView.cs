using System.Threading;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Scenes.ArtworkEnhance.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFragment.Presentation.Components;
using GLOW.Scenes.ArtworkFragment.Presentation.ViewModels;
using GLOW.Scenes.EncyclopediaArtworkDetail.Domain.ValueObjects;
using UIKit;
using UnityEngine;
using UnityEngine.Serialization;
using UnityEngine.UI;
using WonderPlanet.UniTaskSupporter;

namespace GLOW.Scenes.EncyclopediaArtworkDetail.Presentation.Views
{
    public class EncyclopediaArtworkPageView : UIView
    {
        [SerializeField] ArtworkFragmentPanelComponent _fragmentPanelComponent;
        [SerializeField] Button _artworkExpandButton;
        [SerializeField] GameObject _artworkNormalFrame;
        [SerializeField] GameObject _artworkRareFrame;

        [Header("パズルピースのロック画像")]
        [SerializeField] RectTransform _pieceRootObject;
        [SerializeField] RectTransform _pieceParentObject;

        [Header("アニメーション用")]
        [SerializeField] RectTransform _mirrorRectTransform_1;
        [SerializeField] RectTransform _mirrorRectTransform_2;

        [Header("原画のミラー演出")]
        [SerializeField] Animator _animator;

        public bool IsFirstArtworkPage = false;

        // アニメーションのサイズ基準
        const float _mirrorRootWidth = 750.0f;
        const float _mirrorWidth_1 = 252.0f;
        const float _mirrorWidth_2 = 110.0f;
        const float _mirrorDiffPosX = 140.0f;

        // パズルピースのロック画像の基準
        const float _fragumentRootWidth = 630.0f;
        const float _fragumentRootHeight = 630.0f;

        public void Setup(
            ArtworkFragmentPanelViewModel viewModel,
            ArtworkUnlockFlag isUnlock,
            ArtworkGradeMaxLimitFlag isGradeMaxLimit,
            CancellationToken ct)
        {
            _fragmentPanelComponent.Setup(viewModel);
            _artworkExpandButton.interactable = isUnlock;

            SetUpArtworkFrame(isUnlock, isGradeMaxLimit);

            _artworkExpandButton.onClick.RemoveAllListeners();
            if (isGradeMaxLimit && isUnlock)
            {
                _artworkExpandButton.onClick.AddListener(() =>
                {
                    SetAndPlayAnimation();
                });
            }

            if (IsFirstArtworkPage)
            {
                // かけらのロック画像の初期化は、レイアウト後に行う必要があるため、1フレーム遅らせて実行(PageViewの関係)
                DoAsync.Invoke(ct, async cancellationToken =>
                {
                    await UniTask.DelayFrame(1, cancellationToken: cancellationToken);
                    InitializePuzzlePieceLockImage();
                });
            }
            else
            {
                InitializePuzzlePieceLockImage();
            }
        }

        public void InitializeViewTransform()
        {
            RectTransform.offsetMin = Vector2.zero;
            RectTransform.offsetMax = Vector2.zero;
        }

        public void InitializePuzzlePieceLockImage()
        {
            var width = _pieceRootObject.rect.width;
            var height = _pieceRootObject.rect.height;

            var scaleX = width / _fragumentRootWidth;
            var scaleY = height / _fragumentRootHeight;

            _pieceParentObject.transform.localScale = new Vector3(scaleX, scaleY, 1.0f);
        }

        void SetUpArtworkFrame(ArtworkUnlockFlag isUnlock, ArtworkGradeMaxLimitFlag isGradeMaxLimit)
        {
            _artworkNormalFrame.SetActive(!isGradeMaxLimit);
            _artworkRareFrame.SetActive(isUnlock && isGradeMaxLimit);
        }

        void SetAndPlayAnimation()
        {
            // 動作中なら処理しない
            var isMoving = DOTween.IsTweening(_mirrorRectTransform_1) || DOTween.IsTweening(_mirrorRectTransform_2);
            if (isMoving) return;

            // 原画のサイズ取得
            var rect = _fragmentPanelComponent.GetComponent<RectTransform>();
            var width = rect.rect.width;

            // 理想のサイズを計算
            var mirrorWidth_1 = _mirrorWidth_1 * width / _mirrorRootWidth;
            var mirrorWidth_2 = _mirrorWidth_2 * width / _mirrorRootWidth;

            // スタート位置を設定
            var startPosX_1 = -mirrorWidth_1;
            var startPosX_2 = -(mirrorWidth_1 + mirrorWidth_2 + _mirrorDiffPosX);
            _mirrorRectTransform_1.localPosition = new Vector2(startPosX_1, _mirrorRectTransform_1.localPosition.y);
            _mirrorRectTransform_2.localPosition = new Vector2(startPosX_2, _mirrorRectTransform_2.localPosition.y);

            // サイズを設定
            _mirrorRectTransform_1.sizeDelta = new Vector2(mirrorWidth_1, _mirrorRectTransform_1.sizeDelta.y);
            _mirrorRectTransform_2.sizeDelta = new Vector2(mirrorWidth_2, _mirrorRectTransform_2.sizeDelta.y);

            // 終了位置を設定して移動
            var endPosX_1 = width + mirrorWidth_2 + _mirrorDiffPosX + 50.0f;
            var endPosX_2 = width + 50.0f;
            _mirrorRectTransform_1.DOLocalMoveX(endPosX_1, 0.6f).SetEase(Ease.InOutSine);
            _mirrorRectTransform_2.DOLocalMoveX(endPosX_2, 0.6f).SetEase(Ease.InOutSine);
        }
    }
}
