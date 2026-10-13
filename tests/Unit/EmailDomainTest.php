<?php

namespace Tests\Unit;

use App\Support\EmailDomain;
use PHPUnit\Framework\TestCase;

class EmailDomainTest extends TestCase
{
    public function test_extracts_domain_lowercase(): void
    {
        $this->assertSame('students.undip.ac.id', EmailDomain::fromEmail('  Budi@Students.UNDIP.ac.id '));
        $this->assertNull(EmailDomain::fromEmail('tanpa-at'));
        $this->assertNull(EmailDomain::fromEmail('budi@'));
    }

    public function test_candidates_from_specific_to_general_without_generic_suffix(): void
    {
        $this->assertSame(
            ['mhs.ft.undip.ac.id', 'ft.undip.ac.id', 'undip.ac.id'],
            EmailDomain::candidates('budi@mhs.ft.undip.ac.id')
        );
        $this->assertSame(['gmail.com'], EmailDomain::candidates('x@gmail.com'));
    }

    public function test_generic_academic_suffix_is_never_a_candidate(): void
    {
        $this->assertNotContains('ac.id', EmailDomain::candidates('x@undip.ac.id'));
    }

    public function test_name_normalization_ignores_case_and_punctuation(): void
    {
        $this->assertSame(
            EmailDomain::normalizeName('Budi  Santoso'),
            EmailDomain::normalizeName("budi-santoso.")
        );
    }

    public function test_mask(): void
    {
        $this->assertSame('bu***@students.undip.ac.id', EmailDomain::mask('budi1@students.undip.ac.id'));
    }
}
