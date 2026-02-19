using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Modules.Spine.Presentation;
using GLOW.Scenes.GachaAnim.Presentation.ViewModels;
using GLOW.Scenes.InGame.Domain.ScriptableObjects;
using GLOW.Scenes.InGame.Presentation.Field;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.GachaAnim.Presentation.Views.Parts
{
    public class GachaAnimResultComponent : UIObject
    {
        [SerializeField] Animator _animator;
        [Header("ユニット")]
        [SerializeField] UISpineWithOutlineAvatar _avatar;
        [SerializeField] SeriesLogoComponent _logoComponent;
        [SerializeField] IconRarityImage _rarityIcon;
        [SerializeField] CharacterColorIcon _characterColorIcon;
        [SerializeField] UIImage _unitPictureImage;
        [SerializeField] UIImage _unitPictureShadowImage;
        [Header("ユニット/吹き出し")]
        [SerializeField] GachaSpeechBalloonComponent _speechBalloonComponent;
        [SerializeField] UIImage _fukidashiURLeftImage;
        [SerializeField] UIImage _fukidashiURRightImage;
        [SerializeField] UIImage _fukidashiURCenterImage;

        [Header("ユニット/コマエリア")]
        [SerializeField] UIObject _komaArea;

        [Header("アイテム")]
        [SerializeField] UIImage _itemImagePrefab;
        [SerializeField] UIObject _itemImageParent;

        [Header("共通")]
        [SerializeField] UIText _nameText;
        [SerializeField] UIText _nameRedShadowText;
        [SerializeField] UIText _nameBlueShadowText;
        [SerializeField] GameObject _newIconObj;

        static readonly int Rare = Animator.StringToHash("Rare");
        static readonly int StartAnim = Animator.StringToHash("Start");
        static readonly int EndAnim = Animator.StringToHash("End");
        bool _isOutAnimationEnd = false;
        bool _isSkip = false;
        bool _isAllSkip = false;
        bool _isNewFlg = false;

        UIImage _itemImage;

        public void Setup(
            GachaAnimResultViewModel resultViewModel,
            UnitImage unitImage,
            GachaAnimationUnitInfo unitInfo)
        {
            // ユニット情報系(レアリティ、セリフ、画像)のリストを受け取る
            var isItem = resultViewModel.ResourceType != ResourceType.Unit;
            _avatar.Hidden = isItem;
            _rarityIcon.Hidden = isItem;
            _characterColorIcon.Hidden = isItem;
            _logoComponent.gameObject.SetActive(resultViewModel.ResourceType == ResourceType.Unit);

            // アイテムとキャラで表示切り替え
            if (resultViewModel.ResourceType == ResourceType.Unit)
            {
                SetUpAvatar(resultViewModel, unitImage, unitInfo);
            }
            else
            {
                SetUpItem(resultViewModel);
            }

            _newIconObj.SetActive(resultViewModel.NewFlg.Value);
            _isNewFlg = resultViewModel.NewFlg.Value;

            _isOutAnimationEnd = false;
            _isSkip = false;
        }

        void SetUpAvatar(GachaAnimResultViewModel resultViewModel, UnitImage unitImage, GachaAnimationUnitInfo unitInfo)
        {
            _rarityIcon.Setup(resultViewModel.Rarity);
            _logoComponent.Setup(resultViewModel.SeriesLogoImagePath);
            _nameText.SetText(resultViewModel.CharacterName.Value);
            _nameRedShadowText.SetText(resultViewModel.CharacterName.Value);
            _nameBlueShadowText.SetText(resultViewModel.CharacterName.Value);
            _characterColorIcon.SetupCharaColorIcon(resultViewModel.CharacterColor);

            if (resultViewModel.Rarity >= Rarity.UR)
            {
                SetFukidashiInfo(unitInfo);
            }

            // キャラのセリフ表示
            _speechBalloonComponent.Hidden = resultViewModel.SpeechBalloonText == SpeechBalloonText.Empty;
            if (resultViewModel.SpeechBalloonText != SpeechBalloonText.Empty)
            {
                _speechBalloonComponent.Setup(resultViewModel.SpeechBalloonText);
                _speechBalloonComponent.Play();
            }

            _unitPictureImage.Hidden = unitInfo.PictureSprite == null;
            _unitPictureShadowImage.Hidden = unitInfo.PictureSprite == null;
            if (unitInfo.PictureSprite != null)
            {
                // キャラの原画コマと影のスプライト設定
                _unitPictureImage.Sprite = unitInfo.PictureSprite;
                _unitPictureShadowImage.Sprite = unitInfo.PictureSprite;

                // ======= サイズ調整ここから =======
                float aspect = unitInfo.PictureSprite.rect.size.x / unitInfo.PictureSprite.rect.size.y;
                float targetHeight = unitInfo.PictureSprite.rect.size.y;
                float targetWidth = unitInfo.PictureSprite.rect.size.x;

                // 1. まずkomaAreaの縦幅に収める（必要なら）
                if (unitInfo.PictureSprite.rect.size.y > _komaArea.RectTransform.sizeDelta.y)
                {
                    targetHeight = _komaArea.RectTransform.sizeDelta.y;
                    targetWidth = targetHeight * aspect;
                }

                // 2. 横幅がkomaAreaの横幅を超えていたら、横幅で再計算
                float komaAreaWidth = _komaArea.RectTransform.sizeDelta.x;
                if (targetWidth > komaAreaWidth)
                {
                    targetWidth = komaAreaWidth;
                    targetHeight = targetWidth / aspect;
                }

                _unitPictureImage.RectTransform.sizeDelta = new Vector2(targetWidth, targetHeight);
                _unitPictureShadowImage.RectTransform.sizeDelta = new Vector2(targetWidth, targetHeight);
                // ======= サイズ調整ここまで =======
            }

            var skeletonDataAsset = unitImage.SkeletonAnimation.skeletonDataAsset;
            var avatarScale = unitImage.SkeletonScale;
            _avatar.SetSkeleton(skeletonDataAsset);
            _avatar.SetAvatarScale(avatarScale);
            _avatar.Animate(CharacterUnitAnimation.Wait.Name);
            _animator.SetInteger(Rare, (int)resultViewModel.Rarity + 1);
        }

        void SetUpItem(GachaAnimResultViewModel resultViewModel)
        {
            LoadImage(resultViewModel.ItemIconAssetPath).Forget();
            _nameText.SetText(resultViewModel.ItemName.Value);
            _nameRedShadowText.SetText(resultViewModel.ItemName.Value);
            _nameBlueShadowText.SetText(resultViewModel.ItemName.Value);
            _animator.SetInteger(Rare, 0);
        }

        async UniTask LoadImage(PlayerResourceIconAssetPath assetPath)
        {
            // ItemImageの破棄
            DestroyImage();
            // ItemImageの生成
            _itemImage = Instantiate(_itemImagePrefab, _itemImageParent.RectTransform);
            // 画像の読み込み
            await UISpriteUtil.Load(this.destroyCancellationToken, _itemImage.Image, assetPath.Value);
        }

        public async UniTask PlayAnimation(CancellationToken cancellationToken)
        {
            _isSkip = false;
            _animator.SetTrigger(StartAnim);
            await UniTask.WaitUntil(() => _isSkip || (_isAllSkip && !_isNewFlg), cancellationToken: cancellationToken);
        }

        public async UniTask PlayEndAnimation(CancellationToken cancellationToken)
        {
            _isSkip = false;
            _animator.SetTrigger(EndAnim);
            await UniTask.WaitUntil(() => _isSkip || _isOutAnimationEnd, cancellationToken: cancellationToken);
        }

        public void OnAnimationSkip()
        {
            // スキップ時にSEを止める
            if (!_isSkip) StopSoundEffectAndPlaySelect();
            _isSkip = true;
        }

        public void SkipAll()
        {
            // スキップ時にSEを止める
            if (!_isAllSkip) StopSoundEffectAndPlaySelect();

            _isAllSkip = true;
            _isSkip = true;
        }

        void StopSoundEffectAndPlaySelect()
        {
            SoundEffectPlayer.Stop();
            SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
        }

        void EndOutAnimation()
        {
            // ItemImageの破棄
            DestroyImage();

            _isOutAnimationEnd = true;
        }

        void DestroyImage()
        {
            if (_itemImage != null)
            {
                Destroy(_itemImage.gameObject);
                _itemImage = null;
            }
        }

        void SetFukidashiInfo(GachaAnimationUnitInfo unitInfo)
        {
            _fukidashiURLeftImage.Hidden = true;
            _fukidashiURRightImage.Hidden = true;
            _fukidashiURCenterImage.Hidden = true;

            if (unitInfo.FukidashiSetting1.FukidashiSprite != null)
            {
                SetFukidashiPosition(unitInfo.FukidashiSetting1.FukidashiSprite, unitInfo.FukidashiSetting1.FukidashiPosition);
            }

            if (unitInfo.FukidashiSetting2.FukidashiSprite != null)
            {
                SetFukidashiPosition(unitInfo.FukidashiSetting2.FukidashiSprite, unitInfo.FukidashiSetting2.FukidashiPosition);
            }
        }

        void SetFukidashiPosition(Sprite sprite, GachaFukidashiPosition position)
        {
            switch (position)
            {
                case GachaFukidashiPosition.Left:
                    _fukidashiURLeftImage.Hidden = false;
                    _fukidashiURLeftImage.Sprite = sprite;
                    break;
                case GachaFukidashiPosition.Right:
                    _fukidashiURRightImage.Hidden = false;
                    _fukidashiURRightImage.Sprite = sprite;
                    break;
                case GachaFukidashiPosition.Center:
                    _fukidashiURCenterImage.Hidden = false;
                    _fukidashiURCenterImage.Sprite = sprite;
                    break;
            }
        }
    }
}
