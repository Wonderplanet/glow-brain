using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.GachaAnim.Presentation.ViewModels;
using GLOW.Scenes.InGame.Domain.ScriptableObjects;
using UnityEngine;
using UnityEngine.UI;
using WonderPlanet.ResourceManagement;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.GachaAnim.Presentation.Views.Parts
{
    public class GachaAnimResultComponent : UIObject
    {
        [SerializeField] Animator _animator;

        [Header("ユニット")]
        [SerializeField] SeriesLogoComponent _logoComponent;
        [SerializeField] IconRarityImage _rarityIcon;
        [SerializeField] UIImage _unitPictureImage;
        [SerializeField] UIImage _unitPictureShadowImage;
        
        [Header("ユニット/吹き出し")]
        [SerializeField] GachaSpeechBalloonComponent _speechBalloonComponent;
        [SerializeField] UIImage _fukidashiURLeftImage;
        [SerializeField] UIImage _fukidashiURRightImage;
        [SerializeField] UIImage _fukidashiURCenterImage;

        [Header("ユニット/コマエリア")]
        [SerializeField] UIObject _komaArea;
        [SerializeField] UIObject _cutInNameRoot;

        [Header("アイテム")]
        [SerializeField] UIImage _itemImagePrefab;
        [SerializeField] UIObject _itemImageParent;

        [Header("共通")]
        [SerializeField] UIText _nameText;
        [SerializeField] UIText _nameRedShadowText;
        [SerializeField] UIText _nameBlueShadowText;
        [SerializeField] GameObject _newIconObj;
        
        [Header("ユニット/ブラー")]
        [SerializeField] UIImage _unitCutInStandImage;
        [SerializeField] UIImage _unitCutInSilhouetteStandImage;
        [SerializeField] UIImage _unitAttackImage;
        [SerializeField] UIImage _unitStandImage;
        [SerializeField] UILightBlurTextureComponent _uiLightBlurTextureComponent;

        [Header("必殺技名テクスチャ")]
        [SerializeField] UIImage _cutinNameImage;

        static readonly int Rare = Animator.StringToHash("Rare");
        static readonly int RareUp = Animator.StringToHash("RareUp");
        static readonly int StartAnim = Animator.StringToHash("Start");
        static readonly int EndAnim = Animator.StringToHash("End");
        static readonly int SkipTrigger = Animator.StringToHash("SkipOut");
        static readonly int URColorTrigger = Animator.StringToHash("UR_ColorChange");
        bool _isOutAnimationEnd;
        bool _isAllSkip;
        bool _isNewFlg;
        bool _isURRarity;
        ResultPhaseType _currentPhase = ResultPhaseType.None;
        UIImage _itemImage;
        readonly List<IAssetReference> _retainedAssetReferences = new List<IAssetReference>();
        
        protected override void OnDestroy()
        {
            base.OnDestroy();
            ReleaseAllAssets();
        }

        void ReleaseAllAssets()
        {
            foreach (var assetReference in _retainedAssetReferences)
            {
                assetReference?.Release();
            }
            _retainedAssetReferences.Clear();
        }

        // GachaAnimResultStateBehaviourからAnimatorの各ステート開始時に呼ばれる
        public void OnEnterSceneA() => _currentPhase = ResultPhaseType.SceneA;
        public void OnEnterSceneB()
        {
            _currentPhase = ResultPhaseType.SceneB;
            
            // アニメーション内でもSetTriggerしているが、タップでスキップされてしまうため、
            // URレアリティの場合はSceneBで色変化演出を明示的に発火
            if (_isURRarity)
            {
                _animator.SetTrigger(URColorTrigger);
            }
        }

        public void OnEnterSceneC() => _currentPhase = ResultPhaseType.SceneC;
        public void OnCompleted() => _currentPhase = ResultPhaseType.Completed;

        public void Setup(
            GachaAnimResultViewModel resultViewModel,
            GachaAnimationUnitInfo unitInfo,
            IAssetSource assetSource)
        {
            // ユニット情報系(レアリティ、セリフ、画像)のリストを受け取る
            var isItem = resultViewModel.ResourceType != ResourceType.Unit;
            _rarityIcon.Hidden = isItem;
            _logoComponent.gameObject.SetActive(resultViewModel.ResourceType == ResourceType.Unit);
            _newIconObj.SetActive(resultViewModel.NewFlg.Value);
            _isNewFlg = resultViewModel.NewFlg.Value;
            _currentPhase = ResultPhaseType.None;
            _isOutAnimationEnd = false;
            _animator.ResetTrigger(URColorTrigger);
            _isURRarity = resultViewModel.Rarity == Rarity.UR;
            
            // アイテムとキャラで表示切り替え
            if (resultViewModel.ResourceType == ResourceType.Unit)
            {
                SetUpUnitAnimation(resultViewModel, unitInfo, assetSource);
            }
            else
            {
                SetUpItem(resultViewModel);
            }
        }

        void SetUpUnitAnimation(GachaAnimResultViewModel resultViewModel, GachaAnimationUnitInfo unitInfo, IAssetSource assetSource)
        {
            // アニメーションにレア度を設定
            var startDisplayRarity = (int)resultViewModel.DisplayRarity + 1;
            var rarityNum = (int)resultViewModel.Rarity + 1;
            _animator.SetInteger(Rare, startDisplayRarity);
            _animator.SetInteger(RareUp, rarityNum);
            
            _rarityIcon.Setup(resultViewModel.Rarity);
            _logoComponent.Setup(resultViewModel.SeriesLogoImagePath);
            _nameText.SetText(resultViewModel.CharacterName.Value);
            _nameRedShadowText.SetText(resultViewModel.CharacterName.Value);
            _nameBlueShadowText.SetText(resultViewModel.CharacterName.Value);

            // ユニット立ち絵設定
            var standAssetPath = resultViewModel.UnitImageGetStandAssetPath;
            var attackAssetPath = resultViewModel.UnitImageGetAttackAssetPath;
            
            // Animatorがアルファを制御しているため、フェード処理なしで直接スプライトをロード
            LoadSpriteDirectly(assetSource, destroyCancellationToken, _unitCutInStandImage.Image, standAssetPath.Value).Forget();
            LoadSpriteDirectly(assetSource, destroyCancellationToken, _unitCutInSilhouetteStandImage.Image, standAssetPath.Value).Forget();
            LoadSpriteDirectly(assetSource, destroyCancellationToken, _unitAttackImage.Image, attackAssetPath.Value).Forget();
            LoadSpriteDirectly(
                assetSource,
                destroyCancellationToken,
                _unitStandImage.Image,
                standAssetPath.Value,
                () => _uiLightBlurTextureComponent.SetTexture(_unitStandImage.Image.sprite.texture)).Forget();
            SetFukidashiInfo(unitInfo);

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
                AdjustSpriteSize(_unitPictureImage, unitInfo.PictureSprite, _komaArea);
                AdjustSpriteSize(_unitPictureShadowImage, unitInfo.PictureSprite, _komaArea);
            }

            if (unitInfo.TechNameSprite != null)
            {
                _cutinNameImage.Hidden = false;
                _cutinNameImage.Sprite = unitInfo.TechNameSprite;
                AdjustSpriteSize(_cutinNameImage, unitInfo.TechNameSprite, _cutInNameRoot);
            }
            else
            {
                _cutinNameImage.Hidden = true;
            }
        }

        public async UniTask PlayAnimation(CancellationToken cancellationToken)
        {
            _animator.SetTrigger(StartAnim);
            await UniTask.WaitUntil(
                () => _currentPhase == ResultPhaseType.Completed || (_isAllSkip && !_isNewFlg),
                cancellationToken: cancellationToken);
        }

        public async UniTask PlayEndAnimation(CancellationToken cancellationToken)
        {
            _animator.SetTrigger(EndAnim);
            await UniTask.WaitUntil(() => _isOutAnimationEnd, cancellationToken: cancellationToken);
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

        public void OnAnimationSkip()
        {
            StopSoundEffectAndPlaySelect();
            
            if (_currentPhase == ResultPhaseType.SceneC)
            {
                // Completedはタップでのみ遷移させる
                _currentPhase = ResultPhaseType.Completed;
                return;
            }
            
            // スキップトリガーを設定して次のステートへスキップ
            _animator.SetTrigger(SkipTrigger);
        }

        public void SkipAll()
        {
            // スキップ時にSEを止める
            if (!_isAllSkip)
            {
                StopSoundEffectAndPlaySelect();
            }
            
            // 全スキップフラグを立てる
            _isAllSkip = true;
            
            // 新規獲得ではない場合は完了
            if(_isNewFlg == false)
            {
                _currentPhase = ResultPhaseType.Completed;
                return;
            }
            
            // 新規獲得はSceneCの場合はタップと同じ扱いとする
            if (_currentPhase == ResultPhaseType.SceneC)
            {
                _currentPhase = ResultPhaseType.Completed;
                return;
            }
            
            _animator.SetTrigger(SkipTrigger);
        }

        void StopSoundEffectAndPlaySelect()
        {
            SoundEffectPlayer.Stop();
            SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
        }

        async UniTask LoadSpriteDirectly(
            IAssetSource assetSource,
            CancellationToken cancellationToken,
            Image image,
            string assetPath,
            System.Action onComplete = null)
        {
            var spriteReference = await assetSource.GetAsset<Sprite>(cancellationToken, assetPath);
            spriteReference.Retain();
            _retainedAssetReferences.Add(spriteReference);
            image.sprite = spriteReference.Value;
            onComplete?.Invoke();
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

        void AdjustSpriteSize(UIImage targetImage, Sprite sprite, UIObject boundsArea)
        {
            float aspect = sprite.rect.size.x / sprite.rect.size.y;
            float targetHeight = sprite.rect.size.y;
            float targetWidth = sprite.rect.size.x;

            // 縦幅に収める
            if (sprite.rect.size.y > boundsArea.RectTransform.sizeDelta.y)
            {
                targetHeight = boundsArea.RectTransform.sizeDelta.y;
                targetWidth = targetHeight * aspect;
            }

            // 横幅に収める
            if (targetWidth > boundsArea.RectTransform.sizeDelta.x)
            {
                targetWidth = boundsArea.RectTransform.sizeDelta.x;
                targetHeight = targetWidth / aspect;
            }

            targetImage.RectTransform.sizeDelta = new Vector2(targetWidth, targetHeight);
        }

        void SetFukidashiInfo(GachaAnimationUnitInfo unitInfo)
        {
            _fukidashiURLeftImage.Hidden = true;
            _fukidashiURRightImage.Hidden = true;
            _fukidashiURCenterImage.Hidden = true;

            if (unitInfo.FukidashiSetting1.FukidashiSprite != null)
            {
                SetFukidashiPosition(unitInfo.FukidashiSetting1.FukidashiSprite,
                    unitInfo.FukidashiSetting1.FukidashiPosition);
            }

            if (unitInfo.FukidashiSetting2.FukidashiSprite != null)
            {
                SetFukidashiPosition(unitInfo.FukidashiSetting2.FukidashiSprite,
                    unitInfo.FukidashiSetting2.FukidashiPosition);
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
