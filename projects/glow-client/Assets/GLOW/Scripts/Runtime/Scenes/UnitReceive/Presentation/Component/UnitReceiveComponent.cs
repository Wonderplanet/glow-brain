using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Presentation.Components;
using GLOW.Modules.Spine.Presentation;
using GLOW.Scenes.GachaAnim.Presentation.Views.Parts;
using GLOW.Scenes.InGame.Presentation.Field;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.UnitReceive.Presentation.Component
{
    public class UnitReceiveComponent : UIObject
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

        [Header("ユニット/コマエリア")]
        [SerializeField] UIObject _komaArea;

        [Header("共通")]
        [SerializeField] UIText _nameText;
        [SerializeField] UIText _nameRedShadowText;
        [SerializeField] UIText _nameBlueShadowText;

        static readonly int Rare = Animator.StringToHash("Rare");

        public void SetUpUnitImage(
            UnitImage unitImage)
        {
            if (unitImage == null)
            {
                _avatar.gameObject.SetActive(false);
                return;
            }

            var skeletonDataAsset = unitImage.SkeletonAnimation.skeletonDataAsset;
            var avatarScale = unitImage.SkeletonScale;
            _avatar.SetSkeleton(skeletonDataAsset);
            _avatar.SetAvatarScale(avatarScale);
            _avatar.Animate(CharacterUnitAnimation.Wait.Name);
        }

        public void SetRarity(Rarity rarity)
        {
            _rarityIcon.Setup(rarity);
            _animator.SetInteger(Rare, (int)rarity + 1);
        }

        public void SetUpLogoImage(SeriesLogoImagePath seriesLogoImagePath)
        {
            _logoComponent.Setup(seriesLogoImagePath);
        }

        public void SetNameText(CharacterName characterName)
        {
            _nameText.SetText(characterName.ToString());
            _nameRedShadowText.SetText(characterName.ToString());
            _nameBlueShadowText.SetText(characterName.ToString());
        }

        public void SetUnitColor(CharacterColor characterColor)
        {
            _characterColorIcon.SetupCharaColorIcon(characterColor);
        }

        public void SetUnitSpeechBalloon(
            SpeechBalloonText speechBalloonText)
        {
            // キャラのセリフ表示
            _speechBalloonComponent.IsVisible = !speechBalloonText.IsEmpty();
            if (!speechBalloonText.IsEmpty())
            {
                _speechBalloonComponent.Setup(speechBalloonText);
                _speechBalloonComponent.Play();
            }
        }

        public void SetUpUnitPictureImage(UnitCutInKomaAssetPath assetPath)
        {
            if (assetPath.IsEmpty())
            {
                _unitPictureImage.IsVisible = false;
                _unitPictureShadowImage.IsVisible = false;
                return;
            }

            UISpriteUtil.LoadSpriteWithFade(
                _unitPictureImage.Image,
                assetPath.ToString(),
                () =>
                {
                    if (!_unitPictureImage ||
                        !_unitPictureShadowImage ||
                        !_komaArea) return;

                    var sprite = _unitPictureImage.Image.sprite;
                    if (sprite == null)
                    {
                        _unitPictureImage.IsVisible = false;
                        _unitPictureShadowImage.IsVisible = false;
                        return;
                    }

                    _unitPictureShadowImage.Sprite = sprite;
                    _unitPictureShadowImage.IsVisible = true;

                    // キャラの原画コマのサイズを設定 大きい場合は_komaAreaに合わせて縦サイズを制限
                    if (sprite.rect.size.y > _komaArea.RectTransform.sizeDelta.y)
                    {
                        var komaAreaDeltaY = _komaArea.RectTransform.sizeDelta.y;

                        // 原画コマの縦横比を保ったまま縦サイズを設定
                        _unitPictureImage.RectTransform.sizeDelta = new Vector2(
                            komaAreaDeltaY * sprite.rect.size.x / sprite.rect.size.y,
                            komaAreaDeltaY);

                        // 影の画像も同じサイズに設定
                        _unitPictureShadowImage.RectTransform.sizeDelta = new Vector2(
                            komaAreaDeltaY * sprite.rect.size.x / sprite.rect.size.y,
                            komaAreaDeltaY);
                    }
                    else
                    {
                        // キャラの原画コマと影のサイズを設定
                        _unitPictureImage.RectTransform.sizeDelta = sprite.rect.size;
                        _unitPictureShadowImage.RectTransform.sizeDelta = sprite.rect.size;
                    }
                });
        }
    }
}
