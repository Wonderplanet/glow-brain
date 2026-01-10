using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ScriptableObjects;
using GLOW.Scenes.InGame.Presentation.Components;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using UnityEngine;
using WonderPlanet.ResourceManagement;
using WonderPlanet.UniTaskSupporter;
using Zenject;

namespace GLOW.Scenes.InGame.Presentation.Field
{
    /// <summary> Spineでない通常時のゲートスプライト </summary>
    public class OutpostDefaultSpriteView : OutpostSpriteView
    {
        [SerializeField] SpriteRenderer _outpostCover;

        [Inject] BattleEffectManager BattleEffectManager { get; }
        [Inject] IAssetSource AssetSource { get; }

        PageComponent _pageComponent;
        Tween _tween;
        IAssetReference<Sprite> _spriteReference;

        public override void Initialize(
            GameObject spriteRoot,
            BattleSide battleSide,
            OutpostViewInfo viewInfo,
            PageComponent pageComponent)
        {
            base.Initialize(spriteRoot, battleSide, viewInfo, pageComponent);
            _pageComponent = pageComponent;
        }

        public override void SetArtworkSprite(ArtworkAssetPath assetPath)
        {
            DoAsync.Invoke(this.GetCancellationTokenOnDestroy(), async token =>
            {
                _spriteReference = await AssetSource.GetAsset<Sprite>(token, assetPath.Value);
                _spriteReference.Retain();
                _outpostCover.sprite = _spriteReference.Value;
            });
        }

        public override void OnSummonUnit()
        {
            var summonEffectPrefab = BattleSide == BattleSide.Player
                ? ViewInfo.PlayerSummonEffect
                : ViewInfo.EnemySummonEffect;

            if (summonEffectPrefab != null)
            {
                BattleEffectManager.Generate(
                        summonEffectPrefab,
                        SummonEffectRoot.transform)
                    .BindOutpost(SummonEffectRoot.gameObject)
                    .Play();
            }
            else
            {
                // 標準のプレイヤー・敵ゲートでの召喚エフェクト
                // エフェクトをBattleEffectManagerから外してViewInfo側に入れてOutpostSpriteViewに任せてもいいが、
                // 今後別の使用場面が出てくるかもしれないこと。
                // またViewInfoに入れる場合Addressableとして含めることとなり、ダウンロード前のチュートリアルで色々問題があるためこのまま
                var effectId = this.BattleSide == BattleSide.Player
                    ? BattleEffectId.PlayerCharacterSummon
                    : BattleEffectId.EnemyCharacterSummon;

                BattleEffectManager.Generate(effectId, SummonEffectRoot.transform).BindOutpost(SummonEffectRoot).Play();
            }
        }

        public override void OnBreakDown(FieldViewCoordV2 fieldViewPos, Vector3 breakDownEffectOffset)
        {
            var pos = OutpostSpriteRoot.transform.position;
            BattleEffectManager.Generate(BattleEffectId.OutpostHit02, pos).Play();

            BattleEffectManager
                .Generate(
                    BattleEffectId.OutpostBreakDown,
                    BattleEffectManager.EffectLayer,
                    (pos + breakDownEffectOffset).ToVector2())
                .BindOutpost(OutpostSpriteRoot)
                .Play();

            var effectId = this.BattleSide == BattleSide.Player
                ? PageEffectId.PlayerOutpostBreakDown
                : PageEffectId.EnemyOutpostBreakDown;

            _pageComponent.GenerateEffect(effectId, fieldViewPos).Play();

            SoundEffectPlayer.Play(SoundEffectId.SSE_051_008);
        }

        public override void OnRecover()
        {

        }

        public override void OnHitAttacks(bool isDangerHp)
        {
            base.OnHitAttacks(isDangerHp);
            var rootTrans = OutpostSpriteRoot.transform;
            BattleEffectManager.Generate(BattleEffectId.OutpostHit01, rootTrans.position).Play();

            if (isDangerHp)
            {
                BattleEffectManager.Generate(BattleEffectId.OutpostHit02, rootTrans.position).Play();
            }

            _tween?.Kill(true);
            _tween = rootTrans.DOShakePosition(0.5f, 0.2f, 50, 90f, false, true);
        }

        void OnDestroy()
        {
            _spriteReference?.Release();
        }

    }
}
