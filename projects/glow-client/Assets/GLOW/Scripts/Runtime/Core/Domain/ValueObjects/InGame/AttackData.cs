using System;
using System.Collections.Generic;
using System.Linq;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record AttackData(
        TickCount AttackDelay,      // 攻撃が発生するまでの時間（それまでに攻撃モーションがキャンセルされると攻撃が発生しない）
        AttackBaseData BaseData,
        IReadOnlyList<AttackElement> AttackElements)
    {
        public static AttackData Empty { get; } = new(
            TickCount.Empty,
            AttackBaseData.Empty,
            Array.Empty<AttackElement>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public AttackElement MainAttackElement => AttackElements.Count > 0 ? AttackElements[0] : AttackElement.Empty;

        public IReadOnlyList<MasterDataId> GetAllElementIds()
        {
            var ids = new List<MasterDataId>();
            foreach (var element in AttackElements)
            {
                ids.Add(element.Id);
                ids.AddRange(element.SubElements.Select(sub => sub.Id));
            }
            return ids;
        }
    }
}
