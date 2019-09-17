using Ionic.Zlib;
using Org.BouncyCastle.Crypto.Engines;
using Org.BouncyCastle.Crypto.Parameters;
using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Cebd_Decrypt
{
    class Program
    {
        static int readInt32BE(Stream fs)
        {
            byte[] int32spc = new byte[4];
            fs.Read(int32spc, 0, 4);
            int32spc = int32spc.Reverse().ToArray();
            return BitConverter.ToInt32(int32spc, 0);
        }

        static void Main(string[] args)
        {


            if (args[0] == "-d")
            {
                FileStream fs = new FileStream(args[1], FileMode.OpenOrCreate, FileAccess.ReadWrite);

                int keyLen = readInt32BE(fs);
                Console.WriteLine("KeyLen: " + keyLen.ToString());
                byte[] key = new byte[keyLen];
                fs.Read(key, 0, keyLen);

                long Remaining = fs.Length - fs.Position;
                Console.WriteLine("Remaining: " + Remaining.ToString());
                byte[] data = new byte[Remaining];
                byte[] data2 = new byte[Remaining];
                fs.Read(data, 0, (int)Remaining);

                RC4Engine ARC4 = new RC4Engine();
                ARC4.Init(false, new KeyParameter(key));
                ARC4.ProcessBytes(data, 0, data.Length, data2, 0);
                Byte[] DecompressedData = ZlibStream.UncompressBuffer(data2);
                File.WriteAllBytes(args[2], DecompressedData);
            }
            else if(args[0] == "-e")
            {
                FileStream fs = new FileStream(args[2], FileMode.OpenOrCreate, FileAccess.ReadWrite);

                int keyLen = readInt32BE(fs);
                Console.WriteLine("KeyLen: " + keyLen.ToString());
                byte[] key = new byte[keyLen];
                fs.Read(key, 0, keyLen);

                Byte[] PlaintextData = File.ReadAllBytes(args[1]);
                Byte[] CompressedData = ZlibStream.CompressBuffer(PlaintextData);
                Byte[] EncryptedData = new Byte[CompressedData.Length];

                RC4Engine ARC4 = new RC4Engine();
                ARC4.Init(false, new KeyParameter(key));
                ARC4.ProcessBytes(CompressedData, 0, CompressedData.Length, EncryptedData, 0);
                fs.SetLength(fs.Position);
                fs.Write(EncryptedData,0,EncryptedData.Length);
                fs.Close();
            }
          
        }
    }
}
