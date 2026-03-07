using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class StateEffectSourceIdProvider : IStateEffectSourceIdProvider
    {
        StateEffectSourceId _nextStateEffectSourceId = new(1);

        public StateEffectSourceIdProvider()
        {
        }

        public StateEffectSourceIdProvider(StateEffectSourceId initialStateEffectSourceId)
        {
            _nextStateEffectSourceId = initialStateEffectSourceId;
        }

        public StateEffectSourceId GenerateNewId()
        {
            var id = _nextStateEffectSourceId;
            _nextStateEffectSourceId = new StateEffectSourceId(_nextStateEffectSourceId.Value + 1);
            return id;
        }
    }
}
