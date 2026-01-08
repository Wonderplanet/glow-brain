using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class AttackIdProvider : IAttackIdProvider
    {
        AttackId _nextAttackId = new(1);

        public AttackIdProvider()
        {
        }

        public AttackIdProvider(AttackId initialAttackId)
        {
            _nextAttackId = initialAttackId;
        }

        public AttackId GenerateNewId()
        {
            var id = _nextAttackId;
            _nextAttackId = new AttackId(_nextAttackId.Value + 1);
            return id;
        }
    }
}
