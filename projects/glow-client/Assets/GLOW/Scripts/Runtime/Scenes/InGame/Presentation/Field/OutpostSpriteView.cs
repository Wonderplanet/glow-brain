using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ScriptableObjects;
using GLOW.Scenes.InGame.Presentation.Components;
using GLOW.Scenes.InGame.Presentation.Constants;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using UnityEngine;
using Zenject;

namespace GLOW.Scenes.InGame.Presentation.Field
{
    public abstract class OutpostSpriteView : MonoBehaviour, IOutpostSpriteView
    {
        [Header("配置基準とするオブジェクト。\nRootは基本Player側の場合の座標配置として、\n敵の場合は横座標を反転させるようにする\n(反転はプログラム側で行う)")]
        [SerializeField] GameObject _spriteRoot;
        [SerializeField] GameObject _gateMarkRoot;
        [SerializeField] GameObject _summonEffectRoot;
        [SerializeField] GameObject _damageEffectRoot;

        [Inject] BattleEffectManager BattleEffectManager { get; }

        protected GameObject OutpostSpriteRoot { get; private set; }
        protected GameObject SummonEffectRoot => _summonEffectRoot;
        protected BattleSide BattleSide;
        protected OutpostViewInfo ViewInfo;

        public virtual void Initialize(
            GameObject spriteRoot,
            BattleSide battleSide,
            OutpostViewInfo viewInfo,
            PageComponent pageComponent)
        {
            OutpostSpriteRoot = spriteRoot;
            BattleSide = battleSide;
            ViewInfo = viewInfo;

            // 順番依存1.
            var isPlayer = battleSide == BattleSide.Player;
            if (!isPlayer)
            {
                // 敵ゲートの場合各ルートの横座標を反転させる
                var roots = new List<GameObject>()
                    {
                        _spriteRoot,
                        _gateMarkRoot,
                        _summonEffectRoot,
                        _damageEffectRoot
                    }
                    .Where(root => root != null)
                    .Distinct();
                foreach (var root in roots)
                {
                    var localPos = root.transform.localPosition;
                    localPos.x = -localPos.x;
                    root.transform.localPosition = localPos;
                }
            }

            // 順番依存2.未設定のRootにはOutpostSpriteRootを設定
            _summonEffectRoot = _summonEffectRoot != null ? _summonEffectRoot : OutpostSpriteRoot;
            _damageEffectRoot = _damageEffectRoot != null ? _damageEffectRoot : OutpostSpriteRoot;
            _gateMarkRoot = _gateMarkRoot != null ? _gateMarkRoot : OutpostSpriteRoot;

            // 順番依存3.独自の召喚マークが設定されている場合は、召喚マークを生成
            var markPrefab = isPlayer ? viewInfo.PlayerMarkPrefab : viewInfo.EnemyMarkPrefab;
            if (markPrefab != null)
            {
                var setMarkTrans = _gateMarkRoot.transform;
                Instantiate(markPrefab, setMarkTrans.transform);
            }
        }

        public virtual void OnSummonUnit()
        {
            var summonEffectPrefab = BattleSide == BattleSide.Player
                ? ViewInfo.PlayerSummonEffect
                : ViewInfo.EnemySummonEffect;

            if (summonEffectPrefab != null)
            {
                BattleEffectManager.Generate(
                        summonEffectPrefab,
                        _summonEffectRoot.transform)
                    .BindOutpost(_summonEffectRoot.gameObject)
                    .Play();
            }
        }

        public virtual void OnHitAttacks(bool isDangerHp)
        {
            if (ViewInfo.DamageEffect != null)
            {
                BattleEffectManager.Generate(
                        ViewInfo.DamageEffect,
                        _damageEffectRoot.transform)
                    .Play();
            }
        }

        public virtual void PlayAnimation(
            OutpostSDAnimationType animationType,
            OutpostSDAnimationType nextAnimationType,
            bool ignoresPriority,
            Action onCompleted = null) { }

        public virtual void SetArtworkSprite(ArtworkAssetPath assetPath) { }

        public abstract void OnBreakDown(FieldViewCoordV2 fieldViewPos, Vector3 breakDownEffectOffset);
        public abstract void OnRecover();
    }
}
