using System.Collections.Generic;
using GLOW.Core.Domain.Constants;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public static class ImmediateEffectHandlerFactory
    {
        static readonly Dictionary<StateEffectType, IImmediateEffectHandler> Handlers = new();

        public static IImmediateEffectHandler GetHandler(StateEffectType stateEffectType)
        {
            if (Handlers.TryGetValue(stateEffectType, out var handler))
                return handler;

            handler = stateEffectType switch
            {
                StateEffectType.SpecialAttackCoolTimeShorten or StateEffectType.SpecialAttackCoolTimeExtend =>
                    new SpecialAttackCoolTimeHandler(),
                StateEffectType.SummonCoolTimeShorten or StateEffectType.SummonCoolTimeExtend =>
                    new SummonCoolTimeHandler(),
                _ => null
            };

            if (handler != null) Handlers[stateEffectType] = handler;
            return handler;
        }
    }
}

