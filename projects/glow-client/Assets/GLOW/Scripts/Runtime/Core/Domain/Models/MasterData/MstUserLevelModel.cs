using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstUserLevelModel(UserLevel Level, UserExp Exp, UserExp NextLevelExp, Stamina MaxStamina)
    {
        public static MstUserLevelModel Empty { get; } = new (UserLevel.Empty, UserExp.Empty, UserExp.Empty, Stamina.Empty);

        public RelativeUserExp RelativeNextLevelExp => new RelativeUserExp(NextLevelExp.Value - Exp.Value);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public RelativeUserExp ToRelativeUserExp(UserExp exp)
        {
            return new RelativeUserExp(exp.Value - Exp.Value);
        }
    }
}
