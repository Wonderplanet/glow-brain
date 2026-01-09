using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.EncyclopediaUnitDetail.Presentation.Views;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using GLOW.Scenes.InGame.Domain.ScriptableObjects;
using GLOW.Scenes.InGame.Presentation.Components;
using GLOW.Scenes.InGame.Presentation.Field;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using UIKit;
using UnityEngine;
using UnityEngine.EventSystems;
using UnityEngine.UI;

namespace GLOW.Scenes.EncyclopediaUnitSpecialAttack.Presentation.Views
{
    /// <summary>
    /// 91_図鑑
    /// 　91-3_作品別キャラ表示
    /// 　　91-3-1_ヒーローキャラ表示
    /// 　　　91-3-1-1_必殺ワザ再生
    /// </summary>
    public class UnitSpecialAttackPreviewView : UIView, IPointerDownHandler
    {
        [SerializeField] CutInPlayer _cutInPlayer;
        [SerializeField] SpecialAttackPreviewUnitRenderComponent _renderComponent;
        [SerializeField] RawImage _unitRenderImage;
        [SerializeField] RectTransform _mangaEffectCenterRoot;
        [SerializeField] RectTransform _mangaEffectRightRoot;

        public Action OnTap { get; set; }

        public void Setup(
            UnitImage unitImage,
            CharacterColor unitColor,
            IsEncyclopediaSpecialAttackPositionRight isRight,
            IUnitImageContainer unitImageContainer)
        {
            _cutInPlayer.Initialize(unitImageContainer);

            _renderComponent.BuildUnit(unitImage, unitColor, isRight);
            _unitRenderImage.texture = _renderComponent.RenderTexture;
        }

        public async UniTask Play(
            TickCount chargeTime,
            TickCount actionDuration,
            IsEncyclopediaSpecialAttackPositionRight isRight,
            UnitAttackViewInfo attackViewInfo,
            CharacterColor unitColor,
            UnitAssetKey unitAssetKey,
            CancellationToken cancellationToken)
        {
            _renderComponent.PlayAnimation(CharacterUnitAnimation.SpecialAttackCharge);
            await UniTask.Delay(TimeSpan.FromSeconds(chargeTime.ToSeconds()), cancellationToken: cancellationToken);

            BaseBattleEffectView battleEffect = null;
            AbstractMangaEffectComponent mangaEffect = null;

            if (attackViewInfo != null)
            {
                await _cutInPlayer.Play(unitColor, unitAssetKey, attackViewInfo, cancellationToken);

                // エフェクト再生開始前の１フレームが描画されてしまうので、処理タイミングをずらす
                await UniTask.WaitForFixedUpdate();

                battleEffect = _renderComponent.PlayAttackEffect(attackViewInfo);
                if (attackViewInfo.AttackMangaEffect != null)
                {
                    var root = isRight ? _mangaEffectRightRoot : _mangaEffectCenterRoot;
                    mangaEffect = PlayMangaEffect(attackViewInfo, root);
                }
            }

            _renderComponent.PlayAnimation(CharacterUnitAnimation.SpecialAttack);

            await UniTask.Delay(TimeSpan.FromSeconds(actionDuration.ToSeconds()), cancellationToken: cancellationToken);

            if (battleEffect != null)
            {
                battleEffect.Destroy();
            }
            if (mangaEffect != null)
            {
                mangaEffect.Destroy();
            }
        }

        AbstractMangaEffectComponent PlayMangaEffect(UnitAttackViewInfo attackViewInfo, Transform effectRoot)
        {
            var effectGameObject = Instantiate(attackViewInfo.AttackMangaEffect, effectRoot);

            var effect = effectGameObject.GetComponent<AbstractMangaEffectComponent>();
            if (effect == null)
            {
                Destroy(effectGameObject);
                return null;
            }

            effect.RectTransform.localPosition = Vector3.zero;

            effect.Play();
            return effect;
        }

        void IPointerDownHandler.OnPointerDown(PointerEventData eventData)
        {
            OnTap?.Invoke();
        }
    }
}
