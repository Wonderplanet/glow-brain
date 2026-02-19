using Cysharp.Text;
using GLOW.Core.Domain.Constants;

namespace GLOW.Core.Domain.ValueObjects
{
    public record NumberParity(int Value)
    {
        public static NumberParity Empty { get; } = new(0);

        public bool IsEven => Value % 2 == 0;
        public bool IsOdd => Value % 2 != 0;
    }
}
