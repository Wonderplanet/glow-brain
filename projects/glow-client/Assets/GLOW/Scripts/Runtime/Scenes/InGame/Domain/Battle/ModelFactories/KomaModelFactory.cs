using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.PersistentStateKomaEffectModel;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class KomaModelFactory : IKomaModelFactory
    {
        [Inject] IStateEffectSourceIdProvider StateEffectSourceIdProvider { get; }

        public KomaModel Create(MstKomaModel mstKomaModel)
        {
            var stateEffectSourceId = StateEffectSourceIdProvider.GenerateNewId();

            var komaEffects = mstKomaModel.KomaEffectType == KomaEffectType.None
                ? Array.Empty<IKomaEffectModel>()
                : new [] { CreateKomaEffectModel(mstKomaModel, stateEffectSourceId) };

            return new KomaModel(
                mstKomaModel.KomaId,
                stateEffectSourceId,
                komaEffects);
        }

        IKomaEffectModel CreateKomaEffectModel(MstKomaModel mstKomaModel, StateEffectSourceId stateEffectSourceId)
        {
            return mstKomaModel.KomaEffectType switch
            {
                KomaEffectType.Gust => new GustKomaEffectModel(
                    mstKomaModel.KomaId,
                    mstKomaModel.KomaEffectType,
                    mstKomaModel.KomaEffectTargetSide,
                    mstKomaModel.KomaEffectTargetColors,
                    mstKomaModel.KomaEffectTargetRoles,
                    mstKomaModel.KomaEffectParameter1,
                    mstKomaModel.KomaEffectParameter2,
                    mstKomaModel.KomaEffectParameter1.ToTickCount(),
                    TickCount.Zero,
                    mstKomaModel.KomaEffectTargetSide == KomaEffectTargetSide.Enemy ?
                        GustEffectDirection.ToEnemy :
                        GustEffectDirection.ToPlayer),
                // 重複しないコマ外でも継続するStateEffectをコマ内のキャラに毎フレーム付与するコマ効果(毒、やけどなど
                var type when PersistentStateKomaEffectModel.IsPersistentKomaEffect(type) => new PersistentStateKomaEffectModel(
                    mstKomaModel.KomaId,
                    stateEffectSourceId,
                    mstKomaModel.KomaEffectType,
                    mstKomaModel.KomaEffectTargetSide,
                    mstKomaModel.KomaEffectTargetColors,
                    mstKomaModel.KomaEffectTargetRoles,
                    mstKomaModel.KomaEffectParameter1.ToTickCount(),
                    mstKomaModel.KomaEffectParameter2.ToStateEffectParameter()),

                KomaEffectType.Darkness => new DarknessKomaEffectModel(
                    mstKomaModel.KomaId,
                    mstKomaModel.KomaEffectType,
                    mstKomaModel.KomaEffectTargetSide,
                    mstKomaModel.KomaEffectTargetColors,
                    mstKomaModel.KomaEffectTargetRoles,
                    DarknessClearedFlag.False),

                _ => new StateKomaEffectModel(
                    mstKomaModel.KomaId,
                    mstKomaModel.KomaEffectType,
                    mstKomaModel.KomaEffectTargetSide,
                    mstKomaModel.KomaEffectTargetColors,
                    mstKomaModel.KomaEffectTargetRoles,
                    mstKomaModel.KomaEffectParameter1)
            };
        }
    }
}
