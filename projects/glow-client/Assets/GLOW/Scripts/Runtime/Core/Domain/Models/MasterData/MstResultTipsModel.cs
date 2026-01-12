using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;

namespace GLOW.Core.Domain.Models
{
    public record MstResultTipsModel(UserLevel UserLevel, StageResultTips Tips)
    {
        public static MstResultTipsModel Empty { get; } = new (UserLevel.Empty, StageResultTips.Empty);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
